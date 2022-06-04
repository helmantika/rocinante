<?php

namespace rocinante\command\login;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/command/login/Autologin.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * CheckSession verifies whether the client has a cookie with a session ID that matches someone that
 * is stored in the database.
 */
class CheckSession extends \rocinante\controller\Command
{

   /**
    * Reads a cookie with Rocinante session ID. If it's found and there is a user whose session ID 
    * matches the cookie one then autologin process is invoked. Otherwise, it shows login screen.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "login/CheckSession")
      {
         $username = null;

         // Get session ID from the cookie.
         $sessionid = filter_input(INPUT_COOKIE, 'RocinanteSID', FILTER_SANITIZE_STRING);
         if ($sessionid !== null && $sessionid !== false)
         {
            // Check if a session with this ID exists in database.
            $factory = new \rocinante\persistence\PersistenceFactory("User");
            $assembler = new \rocinante\persistence\DomainAssembler($factory);
            $userIdentity = new \rocinante\mapper\identity\Identity(array('Username' => 's', 'SessionId' => 's'), "User");
            $userIdentity->field("SessionId")->eq($sessionid);
            $collection = $assembler->find($userIdentity);
            $object = $collection->first();
            if ($object !== null)
            {
               $username = $object->get('Username');
               \rocinante\command\SessionRegistry::instance()->setUser($username);
            }
         }

         // If there is a user with a valid session ID then go to autologin.
         if ($username !== null)
         {
            $command = new \rocinante\command\login\Autologin();
            $command->execute($this->request);
         }
         // Otherwise, show login screen.
         else
         {
            include("rocinante/view/login.php");
         }
      }
   }

}
