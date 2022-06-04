<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * ReviseTask creates a new revision task whose contents are the same that a given translation task.
 */
class ReviseTask extends \rocinante\controller\Command
{
   
   const NO_ASSIGNED = 0;
   const ASSIGNED_FOR_TRANSLATION = 1;
   const ASSIGNED_FOR_REVISION = 2;
   const ASSIGNED_FOR_BOTH = 3;
   
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('taskid' => array('IsNumeric'),
                               'userid' => array('IsNumeric'));
   
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
    * The TaskContents persistence factory.
    * @var \rocinante\persistence\PersistenceFactory
    */
   private $taskContentsFactory = null;
   
   /**
    * Creates a new revision task whose contents are the same that a given translation task.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/ReviseTask")
      {
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $taskid = \intval($sqlm->escape($this->request->getProperty('taskid')['value']));
            $userid = \intval($sqlm->escape($this->request->getProperty('userid')['value']));
            
            $this->taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
            $this->taskAssembler = new \rocinante\persistence\DomainAssembler($this->taskFactory);
            $identity = $this->taskFactory->getIdentity();
            $identity->field("TaskId")->eq($taskid);
            $collection = $this->taskAssembler->find($identity);
            $task = $collection->first();
            if ($task !== null)
            {
               $taskid = $task->get('TaskId');
               $task->set('TaskId', null);
               $task->set('UserId', $userid);
               $task->set('Date', \date('Y-m-d'));
               $task->set('Type', 'REVISION');
               $task->set('Progress', 0.00);
               $this->taskAssembler->insert($task);
               $this->insertTaskContents($taskid, $task->get('TaskId'));
               
               $tableid = $task->get('TableId');
               if ($tableid === 0)
               {
                  $this->updateAssignedForLua($taskid);
               }
               else
               {
                  $this->updateAssignedForLang($taskid);
               }
               
               $this->updateWorker($task);
               
               echo \json_encode("OK");
            }
         }
      }
   }
   
   /**
    * Creates task contents from a given task.
    * @param int $oldTaskid Task ID new contents will be created from.
    * @param int $newTaskid New task ID.
    */
   private function insertTaskContents($oldTaskid, $newTaskid)
   {
      $this->taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $assembler = new \rocinante\persistence\DomainAssembler($this->taskContentsFactory);
      $identity = $this->taskContentsFactory->getIdentity();
      $identity->field("TaskId")->eq($oldTaskid);
      $collection = $assembler->find($identity);
      $generator = $collection->getGenerator();
      foreach($generator as $object)
      {
         $newTaskContents = $this->taskContentsFactory->getDomainFactory()->createObject(array());
         $newTaskContents->set('TaskId', $newTaskid);
         $newTaskContents->set('TableId', $object->get('TableId'));
         $newTaskContents->set('TextId', $object->get('TextId'));
         $newTaskContents->set('LuaTextId', $object->get('LuaTextId'));
         $newTaskContents->set('SeqId', $object->get('SeqId'));
         $assembler->insert($newTaskContents);
      }
   }
   
   /**
    * Updates IsAssigned flag for every Lua record that belongs to a given task.
    * @param int $taskid A task ID.
    */
   private function updateAssignedForLua($taskid)
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
         $lua->set('IsAssigned', self::ASSIGNED_FOR_BOTH);
         $luaAssembler->update($lua);
      }
   }
   
   /**
    * Updates IsAssigned flag for every Lang record that belongs to a given task.
    * @param type $taskid A task ID.
    */
   private function updateAssignedForLang($taskid)
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
         $lang->set('IsAssigned', self::ASSIGNED_FOR_BOTH);
         $langAssembler->update($lang);
      }
   }
   
   /**
    * Inserts or updates what the user have to do.
    * @param \rocinante\domain\model\Task $newTask The new task.
    */
   private function updateWorker($newTask)
   {
      $tableid = $newTask->get('TableId');
      $workerFactory = new \rocinante\persistence\PersistenceFactory("Worker");
      $workerAssembler = new \rocinante\persistence\DomainAssembler($workerFactory);
      $workerIdentity = $workerFactory->getIdentity();
      $workerIdentity->field("TableId")->eq($tableid)->iand()->field("UserId")->eq($newTask->get('UserId'));
      $collection = $workerAssembler->find($workerIdentity);
      if ($collection->size() !== 0)
      {
         $worker = $collection->first();
         $worker->set($newTask->get('Type') === "TRANSLATION" ? 'IsTranslating' : 'IsRevising', 1);
         $workerAssembler->update($worker);
      } 
      else
      {
         $worker = $workerFactory->getDomainFactory()->createObject(array());
         $worker->set('TableId', $tableid);
         $worker->set('UserId', $newTask->get('UserId'));
         $worker->set($newTask->get('Type') === "TRANSLATION" ? 'IsTranslating' : 'IsRevising', 1);
         $workerAssembler->insert($worker);
      }
   }
}
