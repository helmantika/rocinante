<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/command/translation/MetaTable.php';
require_once 'rocinante/command/task/SelectStartingString.php';

/**
 * InsertTask creates a new task for a given user.
 */
class InsertTask extends \rocinante\controller\Command
{

   use \rocinante\command\translation\MetaTable
   {
      readTables as protected;
   }

   const STATUS_MODE = 1;
   const INCLUSIVE_OFFSET_MODE = 2;
   const EXCLUSIVE_OFFSET_MODE = 3;
   
   // Values for 'IsAssigned' column of Lang and Lua tables.
   const NOT_ASSIGNED = 0;
   const ASSIGNED_FOR_TRANSLATION = 1;
   const ASSIGNED_FOR_REVISION = 2;
   const ASSIGNED_FOR_BOTH = 3;
   
   // Values for 'IsModified' column of Lang and Lua tables.
   const NOT_MODIFIED = 0;
   const MODIFIED = 1;
   const ASSIGNED_FOR_MODIFICATION = 2;

   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('type'    => array('IsTaskType'),
                               'user'    => array('IsNumeric'),
                               'tableid' => array('IsNonEmpty'),
                               'mode'    => array('IsNumeric'),
                               'count'   => array('IsNumeric'));

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager 
    */
   private static $sqlm;
   
   /**
    * The user who assigns the task.
    * @var int
    */
   private $assigner;

   /**
    * The number of strings to translate/revise.
    * @var int
    */
   private $count;

   /**
    * The initial string number to translate/revise.
    * @var int
    */
   private $offset = null;

   /**
    * The table that will be translated/revised.
    * @var int
    */
   private $tableid = null;

   /**
    * The new task ID assigned by database.
    * @var int
    */
   private $taskid = 0;

   /**
    * The Task persistence factory.
    * @var \rocinante\persistence\PersistenceFactory
    */
   private $taskFactory = null;

   /**
    * The task object assembler.
    * @var \rocinante\persistence\DomainAssembler
    */
   private $taskAssembler = null;

   /**
    * Creates a new task.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/InsertTask")
      {
         // Get the current user. He/she is the assigner.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $this->assigner = $session->getUserId();
            
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         // Check that count is a valid number.
         $this->count = \intval($this->request->getProperty('count')['value']);
         if ($this->count < 1)
         {
            $message .= $this->l10n->{"validation"}->{"string-size-invalid"} . "<br />";
         }
         
         // Task type and table ID.
         self::$sqlm = \rocinante\persistence\SqlManager::instance();
         $type = self::$sqlm->escape($this->request->getProperty('type')['value']);
         if ($type !== "UPDATING")
         {
            $this->tableid = \intval(self::$sqlm->escape($this->request->getProperty('tableid')['value']));
         }

         // Check that offset is a valid number.
         if ($this->request->getProperty('mode')['value'] != self::STATUS_MODE)
         {
            $esoTablefactory = new \rocinante\persistence\PersistenceFactory("EsoTable");
            $esoTableAssembler = new \rocinante\persistence\DomainAssembler($esoTablefactory);
            $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'Size' => 'i'), "EsoTable");
            $esoTableIdentity->field("TableId")->eq($this->tableid);
            $collection = $esoTableAssembler->find($esoTableIdentity);
            $size = $collection->first()->get('Size');
            $startingString = new \rocinante\command\task\SelectStartingString($type, $this->tableid);
            $this->offset = $startingString->execute() - 1;
            if ($this->offset < 0 || $this->offset > $size - 1)
            {
               $message .= $this->l10n->{"validation"}->{"string-offset-invalid"} . "<br />";
            }
         }

         // If every field is valid, create a new task.
         if (empty($message))
         {
            // Set the user who task is assigned to.
            $user = \intval(self::$sqlm->escape($this->request->getProperty('user')['value']));
            if ($user === -1)
            {
               $user = $this->assigner;
            }
            
            // Insert new task.
            $this->taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
            $this->taskAssembler = new \rocinante\persistence\DomainAssembler($this->taskFactory);
            $newTask = $this->taskFactory->getDomainFactory()->createObject(array());
            $newTask->set('TableId', $this->tableid);
            $newTask->set('UserId', $user);
            $newTask->set('AssignerId', $this->assigner);
            $newTask->set('Date', \date('Y-m-d'));
            $newTask->set('Type', $type);
            // Size can change if the number of suitable records is less than 'count'.
            // The number will be corrected when tasks contents are assigned.
            $newTask->set('Size', self::$sqlm->escape($this->request->getProperty('count')['value']));
            $this->taskAssembler->insert($newTask);
            $this->taskid = $newTask->get('TaskId');

            // Select and insert contents for the new task.
            if ($this->taskid > 0)
            {
               $records = 0;
               if ($newTask->get('Type') === "UPDATING")
               {
                  $records = $this->createContentsForUpdatingTask($newTask);
               }
               else // TRANSLATION or REVISION
               {
                  switch ($this->request->getProperty('mode')['value'])
                  {
                     case self::STATUS_MODE:
                        $records = $this->createContentsByStatus($newTask);
                        break;
                     case self::INCLUSIVE_OFFSET_MODE:
                        $records = $this->createContentsByInclusiveOffset($newTask);
                        break;
                     case self::EXCLUSIVE_OFFSET_MODE:
                        $records = $this->createContentsByExclusiveOffset($newTask);
                        break;
                  }
               }
               if ($records === 0)
               {
                  $taskIdentity = $this->taskFactory->getIdentity();
                  $taskIdentity->field("TaskId")->eq($this->taskid);
                  $this->taskAssembler->delete($taskIdentity);
                  $message = (string) $this->l10n->{"dialog"}->{"task"}->{"fail-addition"};
               }
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = \sprintf((string) $this->l10n->{"dialog"}->{"task"}->{"success-addition"}, $this->taskid);
         } else
         {
            $array["result"] = "null";
            $array["html"] = $this->l10n->{"validation"}->{"error"} . "<br />" . $message;
         }
         echo \json_encode($array);
      }
   }

   /**
    * Assigns contents to an updating task by means of selecting strings whose status is 'modified'.
    * The selected string status will change to 'assigned for modification'.
    * @param \rocinante\domain\model\Task $newTask The new task.
    * @return int The number of real records that the task has.
    */
   private function createContentsForUpdatingTask(&$newTask)
   {
      $count = \intval($newTask->get('Size'));
      
      // Try to find modified strings at Lang.
      $generator = $this->createContentsForUpdatingTaskFromLang($count);
      // Insert task contents.
      $records = $this->insertTaskContents($newTask->get('TaskId'), $newTask->get('Type'), $generator);
      
      // Id theres's no records, try to find modified strings at Lua.
      if ($records === 0)
      {
         // Update table for the new task.
         $this->tableid = 0;
         $newTask->set('TableId', 0);
         $this->taskAssembler->update($newTask);

         // Insert task contents.
         $generator = $this->createContentsForUpdatingTaskFromLua($count);
         $records = $this->insertTaskContents($newTask->get('TaskId'), $newTask->get('Type'), $generator);
      }
      
      if ($records > 0)
      {
         // Assign real number of records. This number can be different from $count when the number
         // of suitable records is less than requested number.
         if ($records !== $count)
         {
            $newTask->set('Size', $records);
            $this->taskAssembler->update($newTask);
         }
      }
      
      return $records;
   }
   
   /**
    * Assigns contents to a new task by means of selecting strings whose status is 'not translated'
    * or 'not revised'.
    * @param \rocinante\domain\model\Task $newTask The new task.
    * @return int The number of real records that the task has.
    */
   private function createContentsByStatus(&$newTask)
   {
      $generator = null;
      $isTranslation = $newTask->get('Type') === "TRANSLATION";
      $count = \intval($newTask->get('Size'));

      // Lua table
      if ($this->tableid === 0)
      {
         $generator = $this->createContentsByStatusFromLua($isTranslation, $count);
      }
      // Meta table
      else if ($this->tableid < 0xff)
      {
         $generator = $this->createContentsByStatusFromMetaTable($isTranslation, $count);
      }
      // Lang table
      else
      {
         $generator = $this->createContentsByStatusFromLang($isTranslation, $count);
      }

      // Insert task contents.
      $records = $this->insertTaskContents($newTask->get('TaskId'), $newTask->get('Type'), $generator);
      if ($records > 0)
      {
         // Assign real number of records. This number can be different from $count when the number
         // of suitable records is less than requested number.
         if ($records !== $count)
         {
            $newTask->set('Size', $records);
            $this->taskAssembler->update($newTask);
         }

         // Insert or update what the user has to do.
         $this->updateWorker($newTask);
      }
      
      return $records;
   }

   /**
    * Assigns contents to a new task by means of selecting a number of strings from an offset
    * that can include translated/revised strings.
    * @param \rocinante\domain\model\Task $newTask The new task.
    * @return int The number of real records that the task has.
    */
   private function createContentsByInclusiveOffset(&$newTask)
   {
      $generator = null;
      $isTranslation = $newTask->get('Type') === "TRANSLATION";
      $count = \intval($newTask->get('Size'));

      // Lua table
      if ($this->tableid === 0)
      {
         $generator = $this->createContentsByInclusiveOffsetFromLua($isTranslation, $count);
      }
      // Meta table
      else if ($this->tableid < 0xff)
      {
         $generator = $this->createContentsByInclusiveOffsetFromMetaTable($isTranslation, $count);
      }
      // Lang table
      else
      {
         $generator = $this->createContentsByInclusiveOffsetFromLang($isTranslation, $count);
      }

      // Insert task contents.
      $records = $this->insertTaskContents($this->taskid, $newTask->get('Type'), $generator);
      if ($records > 0)
      {
         // Assign real number of records. This number can be different from $count when the number
         // of suitable records is less than requested number.
         if ($records !== $count)
         {
            $newTask->set('Size', $records);
            $this->taskAssembler->update($newTask); // This method resets TaskId
         }

         // Update task progress because some records could be already translated/revised.
         $this->updateTaskProgress($this->taskid);
         
         // Insert or update what the user has to do.
         $this->updateWorker($newTask);
      }
      
      return $records;
   }
   
   /**
    * Assigns contents to a new task by means of selecting a number of strings from an offset
    * that excludes translated/revised strings.
    * @param \rocinante\domain\model\Task $newTask The new task.
    * @return int The number of real records that the task has.
    */
   private function createContentsByExclusiveOffset(&$newTask)
   {
      $generator = null;
      $records = 0;
      $isTranslation = $newTask->get('Type') === "TRANSLATION";
      $count = \intval($newTask->get('Size'));

      // Lua table
      if ($this->tableid === 0)
      {
         $generator = $this->createContentsByExclusiveOffsetFromLua($isTranslation, $count);
      }
      // Meta table
      else if ($this->tableid < 0xff)
      {
         $generator = $this->createContentsByExclusiveOffsetFromMetaTable($isTranslation, $count);
      }
      // Lang table
      else
      {
         $generator = $this->createContentsByExclusiveOffsetFromLang($isTranslation, $count);
      }

      // Insert task contents.
      if ($generator !== null)
      {
         $records = $this->insertTaskContents($this->taskid, $newTask->get('Type'), $generator);
         if ($records > 0)
         {
            // Assign real number of records. This number can be different from $count when the number
            // of suitable records is less than requested number.
            if ($records !== $count)
            {
               $newTask->set('Size', $records);
               $this->taskAssembler->update($newTask); // This method resets TaskId
            }

            // Update task progress because some records could be already translated/revised.
            $this->updateTaskProgress($this->taskid);

            // Insert or update what the user has to do.
            $this->updateWorker($newTask);
         }
      }
      
      return $records;
   }

   /**
    * Assigns contents from the LUA table to a new task by means of selecting strings whose status
    * is 'not translated' or 'not revised'.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByStatusFromLua($isTranslation, $count)
   {
      $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
      $luaAssembler = new \rocinante\persistence\DomainAssembler($luaFactory);
      $luaIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i',
                                                                   'TextId' => 's',
                                                                   'IsAssigned' => 'i',
                                                                   'IsTranslated' => 'i',
                                                                   'IsRevised' => 'i'), "Lua");
      if ($isTranslation)
      {
         $luaIdentity->field("IsTranslated")->eq(0)->iand();
         $luaIdentity->lparen()->field('IsAssigned')->eq(self::NOT_ASSIGNED)->ior()->field('IsAssigned')->eq(self::ASSIGNED_FOR_REVISION)->rparen();
      }
      else
      {
         $luaIdentity->field("IsRevised")->eq(0)->iand()->field("IsTranslated")->eq(1)->iand();
         $luaIdentity->lparen()->field('IsAssigned')->eq(self::NOT_ASSIGNED)->ior()->field('IsAssigned')->eq(self::ASSIGNED_FOR_TRANSLATION)->rparen();         
      }
      $luaIdentity->orderByAsc("TextId")->limit($count);
      $collection = $luaAssembler->find($luaIdentity);
      
      // Update records because they are going to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $isAssigned = \intval($object->get('IsAssigned'));
         $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
         $luaAssembler->update($object);
      }
      
      return $collection->getGenerator();
   }

   /**
    * Assigns contents from the LUA table to a new task by means of selecting a number of strings
    * from an offset that can include translated/revised strings.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByInclusiveOffsetFromLua($isTranslation, $count)
   {
      $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
      $luaAssembler = new \rocinante\persistence\DomainAssembler($luaFactory);
      $luaIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 
                                                                   'TextId' => 's', 
                                                                   'IsAssigned' => 'i',
                                                                   'IsTranslated' => 'i',
                                                                   'IsRevised' => 'i'), "Lua");
      $luaIdentity->orderByAsc("TextId")->limit($this->offset, $count);
      $collection = $luaAssembler->find($luaIdentity);
      
      // Update records because they are going to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $isAssigned = \intval($object->get('IsAssigned'));
         $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
         $luaAssembler->update($object);
      }
      
      return $collection->getGenerator();
   }
   
   /**
    * Assigns contents from the Lua table to a new task by means of selecting a number of 
    * consecutive strings without translated/revised strings.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByExclusiveOffsetFromLua($isTranslation, $count)
   {
      // Find a string that is not translated/revised and $count-1 rows before neither.
      $taskType = $isTranslation ? "IsTranslated" : "IsRevised";
      self::$sqlm->query("SET @count=0");
      $statement  = "SELECT t.* FROM ";
      $statement .= "(SELECT TextId, @count := IF(IsAssigned = 0 AND $taskType = 0, @count+1, 0) AS counter FROM Lua) AS t ";
      $statement .= "WHERE t.counter = $count ORDER BY t.TextId LIMIT 1";
      
      self::$sqlm->query($statement);
      $result = self::$sqlm->fetchAssoc($statement);
      self::$sqlm->close($statement);
      
      if ($result !== null && $result['TextId'] !== null)
      {
         $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
         $luaAssembler = new \rocinante\persistence\DomainAssembler($luaFactory);

         $luaCounter = new \rocinante\mapper\identity\Identity(array('TextId' => 's'), "Lua");
         $luaCounter->count("TextId");
         $luaCounter->field("TextId")->le($result['TextId']);
         $luaCounter->orderByAsc("TextId");
         $collectionCounter = $luaAssembler->find($luaCounter);
         $objectCounter = $collectionCounter->first();
      
         if ($objectCounter !== null)
         { 
            $luaIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i',
                                                                         'TextId' => 's',
                                                                         'IsAssigned' => 'i'), "Lua");
            $luaIdentity->field("TextId")->le($result['TextId']);
            $luaIdentity->orderByAsc("TextId");
            $luaIdentity->limit($objectCounter->get('COUNT(TextId)') - $count, $count);
            $collection = $luaAssembler->find($luaIdentity);
      
            // Update records because they are going to be assigned to a task.
            $generator = $collection->getGenerator();
            foreach ($generator as $object)
            {
               $isAssigned = \intval($object->get('IsAssigned'));
               $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
               $luaAssembler->update($object);
            }
      
            return $collection->getGenerator();
         }
      }
      
      return null;
   }
   
   /**
    * Creates a new task from a metatable by means of selecting strings whose status is 'not
    * translated' or 'not revised'.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByStatusFromMetaTable($isTranslation, $count)
   {
      $metaTable = $this->readTables($this->tableid);
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
      $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i',
                                                                    'TextId' => 'i',
                                                                    'SeqId' => 'i',
                                                                    'IsAssigned' => 'i',
                                                                    'IsTranslated' => 'i',
                                                                    'IsRevised' => 'i'), "Lang");
      if ($isTranslation)
      {
         $langIdentity->field("IsTranslated")->eq(0)->iand();
         $langIdentity->lparen()->field('IsAssigned')->eq(self::NOT_ASSIGNED)->ior()->field('IsAssigned')->eq(self::ASSIGNED_FOR_REVISION)->rparen();
      }
      else
      {
         $langIdentity->field("IsRevised")->eq(0)->iand()->field("IsTranslated")->eq(1)->iand();
         $langIdentity->lparen()->field('IsAssigned')->eq(self::NOT_ASSIGNED)->ior()->field('IsAssigned')->eq(self::ASSIGNED_FOR_TRANSLATION)->rparen();         
      }
      
      $langIdentity->iand()->lparen();
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $langIdentity->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $langIdentity->ior();
         }
      }
      $langIdentity->rparen();
      $langIdentity->orderByAsc("TextId")->orderByAsc("SeqId")->orderByFieldAsc("TableId", \array_values($metaTable));
      $langIdentity->limit($count);
      $collection = $langAssembler->find($langIdentity);
      
      // Update records because they are goint to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $isAssigned = \intval($object->get('IsAssigned'));
         $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
         $langAssembler->update($object);
      }
      
      return $collection->getGenerator();
   }

   /**
    * Assigns contents from a metatable to a new task by means of of selecting a number of strings
    * from an offset that can include translated/revised strings.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByInclusiveOffsetFromMetaTable($isTranslation, $count)
   {
      $metaTable = $this->readTables($this->tableid);
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
      $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i',
                                                                    'TextId' => 'i',
                                                                    'SeqId' => 'i',
                                                                    'IsAssigned' => 'i',
                                                                    'IsTranslated' => 'i',
                                                                    'IsRevised' => 'i'), "Lang");
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $langIdentity->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $langIdentity->ior();
         }
      }
      $langIdentity->orderByAsc("TextId")->orderByAsc("SeqId")->orderByFieldAsc("TableId", \array_values($metaTable));
      $langIdentity->limit($this->offset, $count);
      $collection = $langAssembler->find($langIdentity);
      
      // Update records because they are going to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $isAssigned = \intval($object->get('IsAssigned'));
         $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
         $langAssembler->update($object);
      }
      
      return $collection->getGenerator();
   }
   
   /**
    * Assigns contents from a metatable to a new task by means of selecting a number of consecutive
    * strings without translated/revised strings.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByExclusiveOffsetFromMetaTable($isTranslation, $count)
   {
      $metaTable = $this->readTables($this->tableid);
      $tables = "";
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $tables .= "TableId = " . ($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $tables .= " OR ";
         }
      }
              
      // Find a string that is not translated/revised and its $count-1 rows before neither.
      $taskType = $isTranslation ? "IsTranslated" : "IsRevised";
      self::$sqlm->query("SET @count=0");
      $statement  = "SELECT t.* FROM ";
      $statement .= "(SELECT TableId, TextId, SeqId, @count := IF(IsAssigned = 0 AND $taskType = 0, @count+1, 0) AS counter FROM Lang ";
      $statement .= "WHERE ($tables) ORDER BY TextId, SeqId, FIELD(TableId, " . \implode(",", \array_values($metaTable)) . ")) AS t ";
      $statement .= "WHERE t.counter = $count ORDER BY TextId, SeqId, FIELD(TableId, " . \implode(",", \array_values($metaTable)) . ") LIMIT 1";
      
      self::$sqlm->query($statement);
      $result = self::$sqlm->fetchAssoc($statement);
      self::$sqlm->close($statement);
      
      if ($result !== null && $result['TextId'] !== null)
      {
         $statement  = "SELECT COUNT(*) FROM ";
         $statement .= "(SELECT TableId, TextId, SeqId FROM Lang ";
         $statement .= "WHERE ($tables) ORDER BY TextId, SeqId, FIELD(TableId, " . \implode(",", \array_values($metaTable)) . ")) AS t ";
         $statement .= "WHERE TextId <= " . $result['TextId'];
         
         self::$sqlm->query($statement);
         $offset = self::$sqlm->fetchAssoc($statement);
         self::$sqlm->close($statement);

         if ($offset !== null)
         { 
            $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
            $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
            $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 
                                                                          'TextId' => 's',
                                                                          'SeqId' => 'i',
                                                                          'IsAssigned' => 'i'), "Lang");
            for ($i = 0; $i < \count($metaTable); $i++)
            {
               $langIdentity->field("TableId")->eq($metaTable[$i + 1]);
               if ($i < \count($metaTable) - 1)
               {
                  $langIdentity->ior();
               }
            }
            $langIdentity->orderByAsc("TextId")->orderByAsc("SeqId")->orderByFieldAsc("TableId", \array_values($metaTable));
            $langIdentity->limit($offset['COUNT(*)'] - $count, $count);
            $collection = $langAssembler->find($langIdentity);
      
            // Update records because they are going to be assigned to a task.
            $generator = $collection->getGenerator();
            foreach ($generator as $object)
            {
               $isAssigned = \intval($object->get('IsAssigned'));
               $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
               $langAssembler->update($object);
            }
      
            return $collection->getGenerator();
         }
      }
      
      return null;
   }

   /**
    * Assigns contents from a lang table to a new task by means of selecting strings whose status is
    * 'not translated' or 'not revised'.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByStatusFromLang($isTranslation, $count)
   {
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
      $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i',
                                                                    'TextId' => 'i',
                                                                    'SeqId' => 'i',
                                                                    'IsAssigned' => 'i',
                                                                    'IsTranslated' => 'i',
                                                                    'IsRevised' => 'i'), "Lang");
      $langIdentity->field("TableId")->eq($this->tableid)->iand();
      if ($isTranslation)
      {
         $langIdentity->field("IsTranslated")->eq(0)->iand();
         $langIdentity->lparen()->field('IsAssigned')->eq(self::NOT_ASSIGNED)->ior()->field('IsAssigned')->eq(self::ASSIGNED_FOR_REVISION)->rparen();
      }
      else
      {
         $langIdentity->field("IsRevised")->eq(0)->iand()->field("IsTranslated")->eq(1)->iand();
         $langIdentity->lparen()->field('IsAssigned')->eq(self::NOT_ASSIGNED)->ior()->field('IsAssigned')->eq(self::ASSIGNED_FOR_TRANSLATION)->rparen();         
      }
      $langIdentity->orderByAsc("TextId")->orderByAsc("SeqId")->limit($count);
      $collection = $langAssembler->find($langIdentity);
      
      // Update records because they are goint to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $isAssigned = \intval($object->get('IsAssigned'));
         $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
         $langAssembler->update($object);
      }
      
      return $collection->getGenerator();
   }

   /**
    * Assigns contents from a lang table to a new task by means of of selecting a number of strings
    * from an offset that can include translated/revised strings.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByInclusiveOffsetFromLang($isTranslation, $count)
   {
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
      $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i',
                                                                    'TextId' => 'i',
                                                                    'SeqId' => 'i',
                                                                    'IsAssigned' => 'i',
                                                                    'IsTranslated' => 'i',
                                                                    'IsRevised' => 'i'), "Lang");
      $langIdentity->field("TableId")->eq($this->tableid);
      $langIdentity->orderByAsc("TextId")->orderByAsc("SeqId")->limit($this->offset, $count);
      $collection = $langAssembler->find($langIdentity);
      
      // Update records because they are going to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $isAssigned = \intval($object->get('IsAssigned'));
         $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
         $langAssembler->update($object);
      }
      
      return $collection->getGenerator();
   }
   
   /**
    * Assigns contents from the Lang table to a new task by means of selecting a number of 
    * consecutive strings without translated/revised strings.
    * @param bool $isTranslation Defines whether the task is a translation task.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsByExclusiveOffsetFromLang($isTranslation, $count)
   {
      // Find a string that is not translated/revised and $count-1 rows before neither.
      $taskType = $isTranslation ? "IsTranslated" : "IsRevised";
      self::$sqlm->query("SET @count=0");
      $statement  = "SELECT t.* FROM ";
      $statement .= "(SELECT TableId, TextId, SeqId, @count := IF(IsAssigned = 0 AND $taskType = 0, @count+1, 0) AS counter FROM Lang) AS t ";
      $statement .= "WHERE TableId = $this->tableid AND t.counter = $count ORDER BY t.TableId, t.TextId, t.SeqId LIMIT 1";
      
      self::$sqlm->query($statement);
      $result = self::$sqlm->fetchAssoc($statement);
      self::$sqlm->close($statement);
      
      if ($result !== null && $result['TextId'] !== null && $result['SeqId'] !== null)
      {
         $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
         $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);

         $langCounter = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 
                                                                      'TextId' => 's',
                                                                      'SeqId' => 'i'), "Lang");
         $langCounter->count("TableId");
         $langCounter->field("TableId")->eq($this->tableid)->iand();
         $langCounter->field("TextId")->le($result['TextId'])->iand();
         $langCounter->field("SeqId")->le($result['SeqId']);
         $langCounter->orderByAsc("TextId")->orderByAsc("SeqId");
         $collectionCounter = $langAssembler->find($langCounter);
         $objectCounter = $collectionCounter->first();
      
         if ($objectCounter !== null)
         { 
            $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 
                                                                          'TextId' => 's',
                                                                          'SeqId' => 'i',
                                                                          'IsAssigned' => 'i'), "Lang");
            $langIdentity->field("TableId")->eq($this->tableid)->iand();
            $langIdentity->field("TextId")->le($result['TextId'])->iand();
            $langIdentity->field("SeqId")->le($result['SeqId']);
            $langIdentity->orderByAsc("TextId")->orderByAsc("SeqId");
            $langIdentity->limit($objectCounter->get('COUNT(TableId)') - $count, $count);
            $collection = $langAssembler->find($langIdentity);
      
            // Update records because they are going to be assigned to a task.
            $generator = $collection->getGenerator();
            foreach ($generator as $object)
            {
               $isAssigned = \intval($object->get('IsAssigned'));
               $object->set('IsAssigned', $isAssigned + ($isTranslation ? self::ASSIGNED_FOR_TRANSLATION : self::ASSIGNED_FOR_REVISION ));
               $langAssembler->update($object);
            }
      
            return $collection->getGenerator();
         }
      }
      
      return null;
   }

   /**
    * Assigns contents from a Lang table to a new updating task by means of selecting a number of 
    * strings that are modified.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsForUpdatingTaskFromLang($count)
   {
      // Build the query for Lang.
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
      
      $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 'i', 'SeqId' => 'i', 'IsModified' => 'i', 'Es' => 's'), "Lang");
      $langIdentity->field('IsModified')->eq(1)->iand()->field('Es')->gt("")->limit($count);
      $collection = $langAssembler->find($langIdentity);
      
      // Update records because they are going to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $object->set('IsModified', self::ASSIGNED_FOR_MODIFICATION);
         $langAssembler->update($object);
      }

      return $collection->getGenerator();
   }

   /**
    * Assigns contents from Lua table to a new updating task by means of selecting a number of 
    * strings that are modified.
    * @param bool $count Number of string to select for the task.
    * @return object A generator to iterate over data.
    */
   private function createContentsForUpdatingTaskFromLua($count)
   {
      // Build the query for Lang.
      $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
      $luaAssembler = new \rocinante\persistence\DomainAssembler($luaFactory);
      
      $luaIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 's', 'IsModified' => 'i', 'Es' => 's'), "Lua");
      $luaIdentity->field('IsModified')->eq(1)->iand()->field('Es')->gt("")->limit($count);
      $collection = $luaAssembler->find($luaIdentity);
      
      // Update records because they are going to be assigned to a task.
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $object->set('IsModified', self::ASSIGNED_FOR_MODIFICATION);
         $luaAssembler->update($object);
      }

      return $collection->getGenerator();
   }
   
   /**
    * Creates task contents from data generated by a selection.
    * @param int $taskid Task ID.
    * @param string $type TRANSLATION, REVISION, or UPDATING.
    * @param object $generator A generator to iterate over data.
    * @return int The number of strings added to the task.
    */
   private function insertTaskContents($taskid, $type, $generator)
   {
      $count = 0;
      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsAssembler = new \rocinante\persistence\DomainAssembler($taskContentsFactory);
      foreach ($generator as $object)
      {
         $newTaskContents = $taskContentsFactory->getDomainFactory()->createObject(array());
         $newTaskContents->set('TaskId', $taskid);
         $newTaskContents->set('TableId', $object->get('TableId'));
         $newTaskContents->set($this->tableid === 0 ? 'LuaTextId' : 'TextId', $object->get('TextId'));
         if ($this->tableid !== 0)
         {
            $newTaskContents->set('SeqId', $object->get('SeqId'));
         }
         $newTaskContents->set('Done', $type === "TRANSLATION" ? $object->get('IsTranslated') : $object->get('IsRevised'));
         $taskContentsAssembler->insert($newTaskContents);
         $count++;
      }
      return $count;
   }

   /**
    * Inserts or updates what the user have to do.
    * @param \rocinante\domain\model\Task $newTask The new task.
    */
   private function updateWorker($newTask)
   {
      $workerFactory = new \rocinante\persistence\PersistenceFactory("Worker");
      $workerAssembler = new \rocinante\persistence\DomainAssembler($workerFactory);
      $workerIdentity = $workerFactory->getIdentity();
      $workerIdentity->field("TableId")->eq($this->tableid)->iand()->field("UserId")->eq($newTask->get('UserId'));
      $collection = $workerAssembler->find($workerIdentity);
      if ($collection->size() !== 0)
      {
         $worker = $collection->first();
         $worker->set($newTask->get('Type') === "TRANSLATION" ? 'IsTranslating' : 'IsRevising', 1);
         $workerAssembler->update($worker);
      } else
      {
         $worker = $workerFactory->getDomainFactory()->createObject(array());
         $worker->set('TableId', $this->tableid);
         $worker->set('UserId', $newTask->get('UserId'));
         $worker->set($newTask->get('Type') === "TRANSLATION" ? 'IsTranslating' : 'IsRevising', 1);
         $workerAssembler->insert($worker);
      }
   }

   /**
    * Updates progress of a task.
    * @param int $taskid A task ID.
    */
   private function updateTaskProgress($taskid)
   {  
      $taskIdentity = $this->taskFactory->getIdentity();
      $taskIdentity->field('TaskId')->eq($taskid);
      $collection = $this->taskAssembler->find($taskIdentity);
      $object = $collection->first();
      if ($object !== null)
      {
         // Update task progress.
         $finished = $this->readFinishedStrings($taskid);
         $progress = $finished * 100.0 / $object->get('Size');
         $object->set('Progress', $progress);
         $this->taskAssembler->update($object);
      }
   }

   /**
    * Returns how many string of a given task are finished.
    * @param int $taskid A task ID.
    * @return int A number of finished strings.
    */
   private function readFinishedStrings($taskid)
   {
      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsAssembler = new \rocinante\persistence\DomainAssembler($taskContentsFactory);
      $taskContentsIdentity = new \rocinante\mapper\identity\Identity(array('TaskId' => 'i', 'Done' => 'i'), "TaskContents");
      $taskContentsIdentity->field('TaskId')->eq($taskid)->iand()->field('Done')->eq(1);
      $taskContentsCollection = $taskContentsAssembler->find($taskContentsIdentity);
      return $taskContentsCollection->size();
   }
   
}
