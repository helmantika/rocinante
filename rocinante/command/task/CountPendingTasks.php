<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * CountPendingTasks creates a string that shows how many pending tasks a user has.
 */
class CountPendingTasks extends \rocinante\controller\Command
{

   /**
    * Creates a string that shows how many pending tasks a user has.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/CountPendingTasks")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         $factory = new \rocinante\persistence\PersistenceFactory("Task");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $taskIdentity = new \rocinante\mapper\identity\Identity(array('TaskId' => 'i', 'UserId' => 'i'), "Task");
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();
         $taskIdentity->field('UserId')->eq($userid);
         $collection = $assembler->find($taskIdentity);
         $counter = $collection->size();
         
         if ($counter === 1)
         {
            $string = $l10n->frontpage->{"pending-task"};
         } 
         else if ($counter > 1)
         {
            $string = \sprintf($l10n->frontpage->{"pending-tasks"}, $counter);
         }
         else
         {
            $string = $l10n->frontpage->{"no-pending-tasks"};
         }

         echo $string;
      }
   }

}
