<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * ReassignTask modifies the user who is in charge of a given task.
 */
class ReassignTask extends \rocinante\controller\Command
{
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
    * Modifies the user who is in charge of a given task.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/ReassignTask")
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
               $oldUserid = $task->get('UserId');
               $type = $task->get('Type');
               $task->set('UserId', $userid);
               $task->set('Date', \date('Y-m-d'));
               if ($type === "TRANSLATION" || $type === "REVISION")
               {
                  $this->updateWorkers($oldUserid, $userid, $task);
               }
               
               $this->taskAssembler->update($task);
               echo \json_encode("OK");
            }
         }
      }
   }
   
   /**
    * Removes the user who was in charge of the task from ESO tables, and adds the new user to them.
    * @param int $oldUserid User ID that was in charge.
    * @param int $newUserid User ID that is in charge.
    * @param \rocinante\domain\model\Task $task Table ID that task is bound.
    */
   private function updateWorkers($oldUserid, $newUserid, $task)
   {
      $workerFactory = new \rocinante\persistence\PersistenceFactory("Worker");
      $workerAssembler = new \rocinante\persistence\DomainAssembler($workerFactory);
      
      // Count how many task the old user has in the table.
      $taskIdentity = $this->taskFactory->getIdentity();
      $taskIdentity->field("TableId")->eq($task->get('TableId'))->iand()->field("UserId")->eq($oldUserid);
      $collection = $this->taskAssembler->find($taskIdentity);
      
      // If the user has this task in the table only, remove him/her from ESO tables.
      if ($collection->size() === 1)
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
      
      // Adds the new user to ESO tables.
      $newWorkerIdentity = $workerFactory->getIdentity();
      $newWorkerIdentity->field("TableId")->eq($task->get('TableId'))->iand()->field("UserId")->eq($newUserid);
      $newCollection = $workerAssembler->find($newWorkerIdentity);
      if ($newCollection->size() !== 0)
      {
         $worker = $newCollection->first();
         $worker->set($task->get('Type') === "TRANSLATION" ? 'IsTranslating' : 'IsRevising', 1);
         $workerAssembler->update($worker);
      } else
      {
         $worker = $workerFactory->getDomainFactory()->createObject(array());
         $worker->set('TableId', $task->get('TableId'));
         $worker->set('UserId', $newUserid);
         $worker->set($task->get('Type') === "TRANSLATION" ? 'IsTranslating' : 'IsRevising', 1);
         $workerAssembler->insert($worker);
      }
   }
}
