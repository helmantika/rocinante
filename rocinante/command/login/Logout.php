<?php

namespace rocinante\command\login;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * Logout closes user session.
 */
class Logout extends \rocinante\controller\Command
{
   /**
    * Closes user session.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "login/Logout")
      {
         // Resume session.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();
         
         // Retrieve user information to nullify session ID.
         $persistenceFactory = new \rocinante\persistence\PersistenceFactory("User");
         $domainAssembler = new \rocinante\persistence\DomainAssembler($persistenceFactory);
         $userIdentity = $persistenceFactory->getIdentity();
         $userIdentity->field("UserId")->eq($userid);
         $collection = $domainAssembler->find($userIdentity);
         $user = $collection->first();
         if ($user === null)
         {
            throw new \Exception("User $userid not found in database");
         }

         // Update session ID.
         $user->set('SessionId', null);
         $domainAssembler->update($user);

         // Destroy the cookie.
         if (isset($_COOKIE['RocinanteSID'])) 
         {
            unset($_COOKIE['RocinanteSID']);
            \setcookie('RocinanteSID', '', \time() - 3600);
         }
         
         // Destroy the session.
         $session->destroy();
         
         // Make a response.
         $array["result"] = "ok";
         echo \json_encode($array);
      }
   }
}
