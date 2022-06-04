<?php

namespace rocinante\command\task;

require_once 'rocinante/persistence/SqlManager.php';
require_once 'rocinante/command/translation/MetaTable.php';

/**
 * SelectStartingString finds the last record that is suitable to be the initial string in task 
 * creation. 
 */
class SelectStartingString
{
   use \rocinante\command\translation\MetaTable
   {
      readTables as protected;
   }
   
   const NO_ASSIGNED = 0;
   const ASSIGNED_FOR_TRANSLATION = 1;
   const ASSIGNED_FOR_REVISION = 2;
   const ASSIGNED_FOR_BOTH = 3;
   
   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager 
    */
   private static $sqlm;
   
   /**
    * The type of task: TRANSLATION, or REVISION.
    * @var string 
    */
   private $type;

   /**
    * The table or metatable ID.
    * @var int 
    */
   private $tableid;
   
   /**
    * To find the last record that is suitable to be the initial string in task creation needs
    * the type of task and the table id.
    * @param string $type Type of task: TRANSLATION, or REVISION.
    * @param int $tableid A table or metatable ID.
    */
   public function __construct($type, $tableid)
   {
      $this->type = $type;
      $this->tableid = $tableid;
   }
   
   /**
    * Finds the last record that is suitable to be the initial string in task creation. 
    * @param int $tableid A table ID.
    * @return int A number that indicates a row position.
    */
   public function execute()
   {
      self::$sqlm = \rocinante\persistence\SqlManager::instance();

      // Lua table
      if ($this->tableid === 0)
      {
         $position = $this->lookStartingStringInLua($this->type);
      }
      // Meta table
      else if ($this->tableid < 0xff)
      {
         $position = $this->lookStartingStringInMetaTable($this->type, $this->tableid);
      }
      // Lang table
      else
      {
         $position = $this->lookStartingStringInLang($this->type, $this->tableid);
      }

      return (integer) $position;
   }

   /**
    * Looks for the last record, that is suitable to be the initial string in task creation, in the Lua
    * table.
    * @param string $type Type of task: TRANSLATION, or REVISION.
    * @return int A number that indicates a row position.
    */
   private function lookStartingStringInLua($type)
   {
      $isAssigned = $type === "TRANSLATION" ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION;
      $both = self::ASSIGNED_FOR_BOTH;
      $statement = "SELECT counter.position, counter.IsAssigned
                    FROM (
                    SELECT Lua.IsAssigned, @rownum := @rownum + 1 AS position FROM Lua JOIN (SELECT @rownum := 0) AS start ORDER BY Lua.TextId
                    ) AS counter
                    WHERE counter.IsAssigned = $isAssigned OR counter.IsAssigned = $both
                    ORDER BY counter.position DESC LIMIT 1";
      
      self::$sqlm->query($statement);
      $row = self::$sqlm->fetchAssoc($statement);
      self::$sqlm->close($statement);
      return $row === null ? 1 : $row['position'] + 1;
   }

   /**
    * Looks for the last record, that is suitable to be the initial string in task creation, in a 
    * Metatable.
    * @param string $type Type of task: TRANSLATION, or REVISION.
    * @param int $tableid A metatable ID.
    * @return int A number that indicates a row position.
    */
   private function lookStartingStringInMetaTable($type, $tableid)
   {
      $isAssigned = $type === "TRANSLATION" ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION;
      $both = self::ASSIGNED_FOR_BOTH;
      $metaTable = $this->readTables($tableid);
      $inner  = "SELECT Lang.IsAssigned, @rownum := @rownum + 1 AS position FROM Lang JOIN (SELECT @rownum := 0) AS start WHERE (";
      $inner .= "Lang.TableId = " . \implode(" OR Lang.TableId = ", $metaTable) . ") ORDER BY Lang.TextId, Lang.SeqId, FIELD(Lang.TableId," . \implode(",", $metaTable) . ")";
      $statement = "SELECT counter.position, counter.IsAssigned FROM ($inner) AS counter WHERE counter.IsAssigned = $isAssigned OR counter.IsAssigned = $both ORDER BY counter.position DESC LIMIT 1";
              
      self::$sqlm->query($statement);
      $row = self::$sqlm->fetchAssoc($statement);
      self::$sqlm->close($statement);
      return $row === null ? 1 : $row['position'] + 1;
   }

   /**
    * Looks for the last record, that is suitable to be the initial string in task creation, in a 
    * Lang table.
    * @param string $type Type of task: TRANSLATION, or REVISION.
    * @param int $tableid A table ID.
    * @return int A number that indicates a row position.
    */
   private function lookStartingStringInLang($type, $tableid)
   {
      $isAssigned = $type === "TRANSLATION" ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION;
      $both = self::ASSIGNED_FOR_BOTH;
      $statement = "SELECT counter.position, counter.IsAssigned
                    FROM (
                    SELECT Lang.IsAssigned, @rownum := @rownum + 1 AS position FROM Lang JOIN (SELECT @rownum := 0) AS start WHERE Lang.TableId = ? ORDER BY Lang.TextId, Lang.SeqId
                    ) AS counter
                    WHERE counter.IsAssigned = ? OR counter.IsAssigned = $both
                    ORDER BY counter.position DESC LIMIT 1";
      
      $values[] = &$tableid;
      $values[] = &$isAssigned;
      self::$sqlm->execute($statement, "ii", $values);
      self::$sqlm->storeResult($statement);
      $field = "counter.position";
      $refFields[] = &$field;
      self::$sqlm->bind($statement, $refFields);
      $result = self::$sqlm->fetch($statement, $refFields);
      self::$sqlm->freeResult($statement);      
      self::$sqlm->reset($statement);
      return $result === false ? 1 : $result['position'] + 1;
   }
}
