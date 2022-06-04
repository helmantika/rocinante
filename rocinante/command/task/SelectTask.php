<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * SelectTask retrieves data of a given task.
 */
class SelectTask extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('taskid' => array('IsNumeric'));
      
   /**
    * Retrieves data of a given task.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/SelectTask")
      {
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $taskid = \intval($sqlm->escape($this->request->getProperty('taskid')['value']));
            
            $factory = new \rocinante\persistence\PersistenceFactory("Task");
            $assembler = new \rocinante\persistence\DomainAssembler($factory);
            $identity = $factory->getIdentity();
            $identity->field("TaskId")->eq($taskid);
            $collection = $assembler->find($identity);
            $task = $collection->first();
            if ($task !== null)
            {
               $array = array('type' => $task->get('Type'), 'userid' => $task->get('UserId'));
               echo \json_encode($array);
            }
         }
      }
   }
}
