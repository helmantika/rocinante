<?php

namespace rocinante\command\task;

require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * SelectPupils retrieves a list of users whose advisor is the current user.
 */
class SelectPupils extends \rocinante\controller\Command
{

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;
   
   /**
    * Retrieves a list of users whose advisor is the current user. Also, if a task ID is received, 
    * it retrieves the user ID that is assigned.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/SelectPupils")
      {
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
                  
         // Get current user.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();
         $userType = $session->getType();
         
         // Set current user as the first candidate to assign a task. The user is called "me".
         $pupils[(string) $this->l10n->{"frontpage"}->{"tabs"}->{"tasks"}->{"me"}] = $userid;
         
         $factory = new \rocinante\persistence\PersistenceFactory("Pupil");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $pupilIdentity = $factory->getIdentity();
         $pupilIdentity->field("AdvisorId")->eq($userid);
         $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
         $userIdentity->orderByAsc('Username');
         $pupilIdentity->join($userIdentity, "PupilId", "UserId");
         $collection = $assembler->find($pupilIdentity);
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $pupils[$object->get('User.Username')] = \intval($object->get('User.UserId'));
         }

         // If the advisor is an admin then append the advisor user list.
         if ($userType === "ADMIN")
         {
            $userFactory = new \rocinante\persistence\PersistenceFactory("User");
            $userAssembler = new \rocinante\persistence\DomainAssembler($userFactory);
            $advisorIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's', 'Type' => 's'), "User");
            $advisorIdentity->field("Type")->eq("ADVISOR")->orderByAsc('Username');
            $advisorCollection = $userAssembler->find($advisorIdentity);
            $advisorGenerator = $advisorCollection->getGenerator();
            foreach ($advisorGenerator as $object)
            {
               $pupils[$object->get('Username')] = \intval($object->get('UserId'));
            }
         }
         
         // Get the user ID that is working in a given task.
         $taskid = $this->request->getProperty('taskid');
         if (isset($taskid))
         {
            $taskid = $taskid['value'];
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
         }
         else
         {
            $json = \json_encode($pupils);
         }
         echo $json;
      }
   }

}
