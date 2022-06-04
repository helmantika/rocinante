<?php

namespace rocinante\command\update;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/controller/Request.php';
require_once 'rocinante/command/translation/UpdateEsoTableSize.php';
require_once 'rocinante/command/translation/UpdateEsoTablePercentages.php';

/**
 * UpdateDatabase adds, changes, and deletes strings to be translated from a new set of lang and lua
 * files.
 */
class UpdateDatabase extends \rocinante\controller\Command
{  
   const TASK_LENGTH = 50;
   const CGI_SUCCESS = 10;

   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager 
    */
   private static $sqlm;

   /**
    * The main language.
    * @var string
    */
   private $baselang;

   /**
    * The second language.
    * @var string
    */
   private $extralang;

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;
   
   /**
    * The Lang persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $langFactory = null;

   /**
    * The Lang object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $langAssembler = null;

   /**
    * The Lua persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $luaFactory = null;

   /**
    * The Lua object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $luaAssembler = null;

   /**
    * The Task persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $taskFactory = null;

   /**
    * The Task object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $taskAssembler = null;

   /**
    * The TaskContents persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $taskContentsFactory = null;

   /**
    * The TaskContents object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $taskContentsAssembler = null;

   /**
    * The EsoTable persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $esoTableFactory = null;

   /**
    * The EsoTable object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $esoTableAssembler = null;

   /**
    * The update consists of a lot of steps. The hard work is done by a CGI script.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "update/UpdateDatabase")
      {
         $viewhelper = \rocinante\view\ViewHelper::instance();
         $this->l10n = $viewhelper->getL10n();

         $this->baselang = (string) $viewhelper->getConfig()->{"baselang"};
         $this->extralang = (string) $viewhelper->getConfig()->{"extralang"};

         self::$sqlm = \rocinante\persistence\SqlManager::instance();
         $this->langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
         $this->langAssembler = new \rocinante\persistence\DomainAssembler($this->langFactory);
         $this->luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
         $this->luaAssembler = new \rocinante\persistence\DomainAssembler($this->luaFactory);
         $this->taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
         $this->taskAssembler = new \rocinante\persistence\DomainAssembler($this->taskFactory);
         $this->taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
         $this->taskContentsAssembler = new \rocinante\persistence\DomainAssembler($this->taskContentsFactory);
         $this->esoTableFactory = new \rocinante\persistence\PersistenceFactory("EsoTable");
         $this->esoTableAssembler = new \rocinante\persistence\DomainAssembler($this->esoTableFactory);

         self::$sqlm->query("CALL InsertNewLangStrings()");
         self::$sqlm->query("CALL UpdateModifiedLangStrings()");
         self::$sqlm->query("CALL UpdateDeletedLangStrings()");
         
         self::$sqlm->query("CALL InsertNewLuaStrings()");
         self::$sqlm->query("CALL UpdateModifiedLuaStrings()");
         self::$sqlm->query("CALL UpdateDeletedLuaStrings()");
         
         $this->deleteLangStringsFromTasks();
         $this->deleteLuaStringsFromTasks();

         $this->updateGlossaryCache();

         $this->addEsoTables();
         $this->deleteEsoTables();

         $this->updateEsoTableStats();
         $this->updateStatus();
      }
      
      $response = array("result" => "ok");
      echo \json_encode($response);
   }
   
   /**
    * Removes records that are marked as deleted in Lang table from tasks.
    */
   private function deleteLangStringsFromTasks()
   {
      $tasks = array();

      $identity = $this->langFactory->getIdentity();
      $identity->field('IsDeleted')->eq(1);
      $collection = $this->langAssembler->find($identity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $taskContentsIdentity = $this->taskContentsFactory->getIdentity();
         $taskContentsIdentity->field('TableId')->eq($object->get('TableId'))->iand()
                              ->field('TextId')->eq($object->get('TextId'))->iand()
                              ->field('SeqId')->eq($object->get('SeqId'));
         
         $taskContentCollection = $this->taskContentsAssembler->find($taskContentsIdentity);
         $taskContent = $taskContentCollection->first();
         if ($taskContent !== null)
         {
            // Count how many records are going to be deleted from a given task.
            if (isset($tasks[$taskContent->get('TaskId')]))
            {
               $tasks[$taskContent->get('TaskId')] += 1;
            }
            else
            {
               $tasks[$taskContent->get('TaskId')] = 1;
            }
            $this->taskContentsAssembler->delete($taskContentsIdentity);
         }
      }

      // Update task size and progress.
      $this->updateTask($tasks);
   }

   /**
    * Removes records that are marked as deleted in Lua table from tasks.
    */
   private function deleteLuaStringsFromTasks()
   {
      $tasks = array();

      $identity = $this->luaFactory->getIdentity();
      $identity->field('IsDeleted')->eq(1);
      $collection = $this->luaAssembler->find($identity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $taskContentsIdentity = $this->taskContentsFactory->getIdentity();
         $taskContentsIdentity->field('TableId')->eq(0)->iand()->field('LuaTextId')->eq($object->get('TextId'));
         
         $taskContentCollection = $this->taskContentsAssembler->find($taskContentsIdentity);
         $taskContent = $taskContentCollection->first();
         if ($taskContent !== null)
         {
            // Count how many records are going to be deleted from a given task.
            if (isset($tasks[$taskContent->get('TaskId')]))
            {
               $tasks[$taskContent->get('TaskId')] += 1;
            }
            else
            {
               $tasks[$taskContent->get('TaskId')] = 1;
            }
            $this->taskContentsAssembler->delete($taskContentsIdentity);
         }
      }
      
      // Update task size and progress.
      $this->updateTask($tasks);    
   }

   /**
    * Updates size and progress of tasks.
    * @param array $tasks An array where keys are task IDs and values are how many records were 
    * deleted.
    */
   private function updateTask(array $tasks)
   {
      // Update task size and progress.
      foreach ($tasks as $taskid => $deletedRecords)
      {
         $taskIdentity = $this->taskFactory->getIdentity();
         $taskIdentity->field('TaskId')->eq($taskid);
         $collection = $this->taskAssembler->find($taskIdentity);
         $object = $collection->first();
         if ($object !== null)
         {
            $newSize = $object->get('Size') - $deletedRecords;

            // If size is 0 then delete the task.
            if ($newSize <= 0)
            {
               $this->taskAssembler->delete($taskIdentity);
            } 
            else
            {
               // Update task size.
               $object->set('Size', $newSize);
               // Update task progress.
               $finished = $this->readFinishedStrings($taskid);
               $progress = $finished * 100.0 / $object->get('Size');
               $object->set('Progress', $progress);
               $this->taskAssembler->update($object);
            }
         }
      }
   }

   /**
    * Returns how many string of a given task are finished.
    * @param int $taskid A task ID.
    * @return int A number of finished strings.
    */
   private function readFinishedStrings($taskid)
   {
      $taskContentsIdentity = new \rocinante\mapper\identity\Identity(array('TaskId' => 'i', 'Done' => 'i'), "TaskContents");
      $taskContentsIdentity->field('TaskId')->eq($taskid)->iand()->field('Done')->eq(1);
      $taskContentsCollection = $this->taskContentsAssembler->find($taskContentsIdentity);
      return $taskContentsCollection->size();
   }

   /**
    * Binds glossary terms to new and modified records.
    */
   private function updateGlossaryCache()
   {
      $query = "CALL CacheLangAfterUpdate()";
      self::$sqlm->query($query);
   }

   /**
    * Add new ESO tables.
    */
   private function addEsoTables()
   {
      // Find out whether there are new tables or not.
      $query = "SELECT Result.TableId
                FROM (
                SELECT TableId, SUM(NOT(IsNew = 1)) = 0 AS NewTable FROM Lang GROUP BY TableId
                ) AS Result 
                WHERE Result.NewTable = 1";

      self::$sqlm->query($query);
      while ($row = self::$sqlm->fetchAssoc($query))
      {
         $raw[] = $row;
      }
      self::$sqlm->close($query);

      // If there are new tables...
      if (isset($raw))
      {
         // Get the last table number.
         $lastTableQuery = "SELECT MAX(Number) AS LastTable from EsoTable";
         self::$sqlm->query($lastTableQuery);
         $row = self::$sqlm->fetchAssoc($lastTableQuery);
         self::$sqlm->close($lastTableQuery);
         $lastTable = \intval($row['LastTable']);

         // Create every new table.
         foreach ($raw as $table)
         {
            $tableid = $table['TableId'];
            $langCounter = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "Lang");
            $langCounter->count("TableId")->field("TableId")->eq($tableid);
            $object = $this->langAssembler->find($langCounter)->first();
            $size = \intval($object->get('COUNT(TableId)'));

            $esoTable = $this->esoTableFactory->getDomainFactory()->createObject(array());
            $esoTable->set('TableId', $tableid);
            $esoTable->set('Number', ++$lastTable);
            $esoTable->set('Description', $this->l10n->frontpage->tabs->{"master-table"}->{"new-table"});
            $esoTable->set('Size', $size);
            $esoTable->set('New', $size);
            $this->esoTableAssembler->insert($esoTable);
         }
      }
   }
   
   /**
    * Deletes old ESO tables and old records. Old records are marked as "deleted".
    */
   private function deleteEsoTables()
   {
      // Find out whether there are tables whose records are marked as deleted or not.
      $query = "SELECT Result.TableId
                FROM (
                SELECT TableId, SUM(NOT(IsDeleted = 1)) = 0 AS NewTable FROM Lang GROUP BY TableId
                ) AS Result 
                WHERE Result.NewTable = 1";

      self::$sqlm->query($query);
      while ($row = self::$sqlm->fetchAssoc($query))
      {
         $raw[] = $row;
      }
      self::$sqlm->close($query);

      // If there are tables with no records, delete every old table.
      if (isset($raw))
      {
         foreach ($raw as $table)
         {
            $tableid = $table['TableId'];
            $identity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "EsoTable");
            $identity->field('TableId')->eq($tableid);
            $this->esoTableAssembler->delete($identity);
         }
         
         // Now delete old records.
         $langIdentity = $this->langFactory->getIdentity();
         $langIdentity->field("IsDeleted")->eq(1);
         $this->langAssembler->delete($langIdentity);

         $luaIdentity = $this->luaFactory->getIdentity();
         $luaIdentity->field("IsDeleted")->eq(1);
         $this->luaAssembler->delete($luaIdentity);
      }
   }

   /**
    * Updates size, percentage of translated strings, and percentage of revised strings of every 
    * ESO table.
    */
   private function updateEsoTableStats()
   {
      $request1 = new \rocinante\controller\Request();
      $request1->setProperty('cmd', 'translation/UpdateEsoTableSize');
      $command1 = new \rocinante\command\translation\UpdateEsoTableSize();
      $command1->execute($request1);
      
      $request2 = new \rocinante\controller\Request();
      $request2->setProperty('cmd', 'translation/UpdateEsoTablePercentages');
      $command2 = new \rocinante\command\translation\UpdateEsoTablePercentages();
      $command2->execute($request2);
   }
   
   /**
    * Counts the number of strings, the number of translated strings, and calculates their 
    * ratio.
    */
   private function updateStatus()
   {
      $counter1 = new \rocinante\mapper\identity\Identity(array('TextId' => 's'), "Lua");
      $counter1->count("TextId");
      $object1 = $this->luaAssembler->find($counter1)->first();
      $luaSize = \intval($object1->get('COUNT(TextId)'));
      
      $counter2 = new \rocinante\mapper\identity\Identity(array('TextId' => 'i'), "Lang");
      $counter2->count("TextId");
      $object2 = $this->langAssembler->find($counter2)->first();
      $langSize = \intval($object2->get('COUNT(TextId)'));
      
      $counter3 = new \rocinante\mapper\identity\Identity(array('TextId' => 's', 'IsTranslated' => 'i'), "Lua");
      $counter3->count("TextId")->field("IsTranslated")->eq(1);
      $object3 = $this->luaAssembler->find($counter3)->first();
      $translatedLua = \intval($object3->get('COUNT(TextId)'));
      
      $counter4 = new \rocinante\mapper\identity\Identity(array('TextId' => 'i', 'IsTranslated' => 'i'), "Lang");
      $counter4->count("TextId")->field("IsTranslated")->eq(1);
      $object4 = $this->langAssembler->find($counter4)->first();
      $translatedLang = \intval($object4->get('COUNT(TextId)'));
      
      $size = $luaSize + $langSize;
      $translated = $translatedLua + $translatedLang;
      $percentage = (double) $translated * 100.0 / (double) $size;
      
      $factory = new \rocinante\persistence\PersistenceFactory("Status");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $identity->field("StatusId")->eq(0);
      $collection = $assembler->find($identity);
      $row = $collection->first();
      if ($row !== null)
      {
         $row->set('Total', $size);
         $row->set('Translated', $translated);
         $row->set('Percentage', $percentage);
         $assembler->update($row);
      }
   }
}
