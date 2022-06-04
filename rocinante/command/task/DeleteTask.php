<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * DeleteTask removes a task.
 */
class DeleteTask extends \rocinante\controller\Command
{

   const NO_ASSIGNED = 0;
   const ASSIGNED_FOR_TRANSLATION = 1;
   const ASSIGNED_FOR_REVISION = 2;
   const ASSIGNED_FOR_BOTH = 3;
   
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
    * Removes a task.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/DeleteTask")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         // Retrieve the task by means of its ID.
         $taskid = $this->request->getProperty('taskid');
         $this->taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
         $this->taskAssembler = new \rocinante\persistence\DomainAssembler($this->taskFactory);
         $taskIdentity = $this->taskFactory->getIdentity();
         $taskIdentity->field("TaskId")->eq($taskid);
         $collection = $this->taskAssembler->find($taskIdentity);
         $task = $collection->first();
         if ($task !== null)
         {
            $userid = $task->get('UserId');
            $this->taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
            $this->taskContentsAssembler = new \rocinante\persistence\DomainAssembler($this->taskContentsFactory);
            
            // Remove user from ESO tables.
            $this->updateWorkers($userid, $task);
            
            // Set IsAssigned to 0 for Lua or Lang records.
            $type = $task->get('Type');
            $tableid = $task->get('TableId');
            if ($tableid === 0)
            {
               $this->removeAssignedFromLua($type, $taskid);
            }
            else
            {
               $this->removeAssignedFromLang($type, $taskid);
            }
            
            // Delete task contents.
            $taskContentsIdentity = $this->taskContentsFactory->getIdentity();
            $taskContentsIdentity->field("TaskId")->eq($taskid);
            $result = $this->taskContentsAssembler->delete($taskContentsIdentity);
            
            // Delete task.
            if ($result !== -1)
            {
               $result = $this->taskAssembler->delete($taskIdentity);
            }
         }
         
         // Make a response.
         if ($result !== -1)
         {
            $array["result"] = "ok";
            $array["html"] = \sprintf((string) $l10n->{"dialog"}->{"task"}->{"success-deletion"}, $taskid);
         }
         else
         {
            $array["result"] = "null";
         }
         echo \json_encode($array);
      }
   }
   
   /**
    * Removes IsAssigned flag from every Lua record that belongs to a given task.
    * @param string $type Type of task.
    * @param int $taskid A task ID.
    */
   private function removeAssignedFromLua($type, $taskid)
   {
      $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
      $luaAssembler = new \rocinante\persistence\DomainAssembler($luaFactory);
    
      $taskContentsIdentity = $this->taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TaskId")->eq($taskid);
      $luaIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 's', 'IsAssigned' => 'i'), "Lua");
      $luaIdentity->join($taskContentsIdentity, "Lua.TextId", "TaskContents.LuaTextId");
      $collection = $luaAssembler->find($luaIdentity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $lua = $luaFactory->getDomainFactory()->createObject(array());
         $lua->set('TableId', $object->get('Lua.TableId'));
         $lua->set('TextId', $object->get('Lua.TextId'));
         $isAssigned = \intval($object->get('Lua.IsAssigned'));
         if ($type === "TRANSLATION")
         {
            $lua->set('IsAssigned', $isAssigned - self::ASSIGNED_FOR_TRANSLATION);
         }
         else if ($type === "REVISION")
         {
            $lua->set('IsAssigned', $isAssigned - self::ASSIGNED_FOR_REVISION);
         }
         $luaAssembler->update($lua);
      }
   }
   
   /**
    * Removes IsAssigned flag from every Lang record that belongs to a given task.
    * @param string $type Type of task.
    * @param type $taskid A task ID.
    */
   private function removeAssignedFromLang($type, $taskid)
   {
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
    
      $taskContentsIdentity = $this->taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TaskId")->eq($taskid);
      $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 'i', 'SeqId' => 'i', 'IsAssigned' => 'i'), "Lang");
      $langIdentity->join($taskContentsIdentity, array("Lang.TableId", "Lang.TextId", "Lang.SeqId"), array("TaskContents.TableId", "TaskContents.TextId", "TaskContents.SeqId"));
      $collection = $langAssembler->find($langIdentity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $lang = $langFactory->getDomainFactory()->createObject(array());
         $lang->set('TableId', $object->get('Lang.TableId'));
         $lang->set('TextId', $object->get('Lang.TextId'));
         $lang->set('SeqId', $object->get('Lang.SeqId'));
         $isAssigned = \intval($object->get('Lang.IsAssigned'));
         if ($type === "TRANSLATION")
         {
            $lang->set('IsAssigned', $isAssigned - self::ASSIGNED_FOR_TRANSLATION);
         }
         else if ($type === "REVISION")
         {
            $lang->set('IsAssigned', $isAssigned - self::ASSIGNED_FOR_REVISION);
         }
         $langAssembler->update($lang);
      }
   }
   
   /**
    * Removes the user who was in charge of the task from ESO tables, and adds the new user to them.
    * @param int $oldUserid User ID that was in charge.
    * @param \rocinante\domain\model\Task $task Table ID that task is bound.
    */
   private function updateWorkers($oldUserid, $task)
   {
      $workerFactory = new \rocinante\persistence\PersistenceFactory("Worker");
      $workerAssembler = new \rocinante\persistence\DomainAssembler($workerFactory);
      
      // Count how many task the old user has in the table.
      $taskIdentity = $this->taskFactory->getIdentity();
      $taskIdentity->field("TableId")->eq($task->get('TableId'))->iand()->field("UserId")->eq($oldUserid);
      $collection = $this->taskAssembler->find($taskIdentity);
      
      // If the user has a task in the table only (or one task of every kind), remove him/her from ESO tables.
      if ($collection->size() === 1 || $collection->size() === 2)
      {
         $oldWorkerIdentity = $workerFactory->getIdentity();
         $oldWorkerIdentity->field("TableId")->eq($task->get('TableId'))->iand()->field("UserId")->eq($oldUserid);
         $oldCollection = $workerAssembler->find($oldWorkerIdentity);
         $worker = $oldCollection->first();
         if ($worker !== null)
         {
            $type = $task->get('Type');
            if (($type === "TRANSLATION" && $worker->get('IsRevising') === 1) ||
                ($type === "REVISION" && $worker->get('IsTranslating') === 1))
            {
               $worker->set($task->get('Type') === "TRANSLATION" ? 'IsTranslating' : 'IsRevising', 0);
               $workerAssembler->update($worker);
            }
            else if ($type === "TRANSLATION" || $type === "REVISION")
            {
               $workerAssembler->delete($oldWorkerIdentity);
            }
         }
      }
   }
}
