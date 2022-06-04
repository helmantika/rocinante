<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateCurrentUser changes data of the current user account.
 */
class UpdateCurrentUser extends \rocinante\controller\Command
{

   /**
    * Specifies how to validate fields coming from a request. 
    * @var array  
    */
   private $validation = array('username'  => array('IsNonEmpty', 'IsMaxLength(20)'),
                               'name'      => array('IsNonEmpty', 'IsMaxLength(30)'),
                               'gender'    => array('IsGender'),
                               'email'     => array('IsEmail'));

   /**
    * Changes data of the user account.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/UpdateCurrentUser")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);

         // Check passwords if they are set.
         $password = $this->request->getProperty('password')['value'];
         $passwordv = $this->request->getProperty('passwordv')['value'];
         if (!empty($password) && !empty($passwordv))
         {
            $passwordValidation = array('password'  => array('IsMinLength(6)'));
            $message .= \rocinante\command\Validation::validate($passwordValidation, $this->request);

            // Check that passwords are equal.
            if (empty($message) && $password !== $passwordv)
            {
               $message .= $l10n->{"validation"}->{"passwords-mismatch"} . "<br />";
            }
         }

         // Retrieve the user by means of his/her username.
         $username = null;
         $reload = false;
         if (empty($message))
         {
            $username = $this->request->getProperty('username')['value'];
            $factory = new \rocinante\persistence\PersistenceFactory("User");
            $assembler = new \rocinante\persistence\DomainAssembler($factory);
            $userIdentity = $factory->getIdentity();
            $userIdentity->field("Username")->eq($username);
            $collection = $assembler->find($userIdentity);
            $user = $collection->first();
            if ($user === null)
            {
               $message .= \sprintf($l10n->{"validation"}->{"username-not-found"} . "<br />", $username);
            }
            // If everything is right, modify the user and update the database.
            else
            {               
               $sqlm = \rocinante\persistence\SqlManager::instance();
               $user->set('Username', $sqlm->escape($this->request->getProperty('username')['value']));
               if (!empty($password))
               {
                  $user->set('Password', \password_hash($sqlm->escape($this->request->getProperty('password')['value']), \PASSWORD_DEFAULT));
               }
               $user->set('FirstName', $sqlm->escape($this->request->getProperty('name')['value']));
               $user->set('Gender', $sqlm->escape($this->request->getProperty('gender')['value']));
               $user->set('Email', $sqlm->escape($this->request->getProperty('email')['value']));
               $theme = $sqlm->escape($this->request->getProperty('theme')['value']);
               if ($theme !== $user->get('Theme'))
               {
                  $user->set('Theme', $theme);
                  $reload = true;
               }
               $assembler->update($user);
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = (string) $l10n->{"dialog"}->{"user-account-success-modification"};
         }
         else
         {
            $array["result"] = "null";
            $array["html"] = $l10n->{"validation"}->{"error"} . "<br />" . $message;
         }
         $array["reload"] = $reload;
         echo \json_encode($array);
      }
   }

}
