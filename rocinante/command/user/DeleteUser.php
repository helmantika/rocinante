<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * DeleteUser removes a user account.
 */
class DeleteUser extends \rocinante\controller\Command
{

   /**
    * Specifies how to validate fields coming from a request. 
    * @var array  
    */
   private $validation = array('username'  => array('IsNonEmpty', 'IsMaxLength(20)'));

   /**
    * Removes a user account.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/DeleteUser")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);

         // Retrieve the user by means of his/her username.
         $username = null;
         if (empty($message))
         {
            $username = $this->request->getProperty('username')['value'];
            $factory = new \rocinante\persistence\PersistenceFactory("User");
            $assembler = new \rocinante\persistence\DomainAssembler($factory);
            $userIdentity = $factory->getIdentity();
            $userIdentity->field("Username")->eq($username);
            $result = $assembler->delete($userIdentity);
            if ($result < 0)
            {
               $message .= "ERROR 101";
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = \sprintf((string) $l10n->{"dialog"}->{"user"}->{"success-deletion"}, $username);
         }
         else
         {
            $array["result"] = "null";
            $array["html"] = $l10n->{"validation"}->{"error"} . "<br />" . $message;
         }
         echo \json_encode($array);
      }
   }
}
