<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * SelectUsername returns the name of the current user.
 */
class SelectUsername extends \rocinante\controller\Command
{

   /**
    * Retrieves the name of the current user.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/SelectUsername")
      {
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();

         $userFactory = new \rocinante\persistence\PersistenceFactory("User");
         $userAssembler = new \rocinante\persistence\DomainAssembler($userFactory);
         $userIdentity = $userFactory->getIdentity();
         $userIdentity->field("UserId")->eq($userid);
         $collection = $userAssembler->find($userIdentity);
         $object = $collection->first();
         if ($object === null)
         {
            throw new \Exception("User not found");
         }

         $result = array("username" => $object->get('Username'), "type" => $object->get('Type'));
         echo \json_encode($result);
      }
   }

}
