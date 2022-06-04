<?php

namespace rocinante\command\task;

require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * SelectUsernames retrieves a username list of every user.
 */
class SelectUsernames extends \rocinante\controller\Command
{

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * Retrieves a username list of every user. Also, if a task ID is received, it retrieves the 
    * user ID that is assigned.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/SelectUsernames")
      {
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         // Get current user.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();

         // Set current user as the first candidate to assign a task. The user is called "me".
         $pupils[(string) $this->l10n->{"frontpage"}->{"tabs"}->{"tasks"}->{"me"}] = $userid;

         $factory = new \rocinante\persistence\PersistenceFactory("User");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
         $userIdentity->field('UserId')->neq($userid)->orderByAsc('Username');
         $collection = $assembler->find($userIdentity);
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $pupils[$object->get('Username')] = \intval($object->get('UserId'));
         }

         // Get the user ID that is working in a given task.
         $taskid = $this->request->getProperty('taskid')['value'];
         if (\is_numeric($taskid))
         {
            $taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
            $taskAssembler = new \rocinante\persistence\DomainAssembler($taskFactory);
            $taskIdentity = $taskFactory->getIdentity();
            $taskIdentity->field("TaskId")->eq($taskid);
            $collection = $taskAssembler->find($taskIdentity);
            $task = $collection->first();
            if ($task !== null)
            {
               $json = \json_encode(array('pupils' => $pupils, 'userid' => $task->get('UserId')));
            }
         } else
         {
            $json = \json_encode($pupils);
         }
         echo $json;
      }
   }

}
