<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateUser changes data of an user account.
 */
class UpdateUser extends \rocinante\controller\Command
{

   /**
    * Specifies how to validate fields coming from a request. 
    * @var array  
    */
   private $validation = array('username'  => array('IsNonEmpty', 'IsMaxLength(20)'),
                               'type'      => array('IsUserType'),
                               'name'      => array('IsNonEmpty', 'IsMaxLength(30)'),
                               'gender'    => array('IsGender'),
                               'email'     => array('IsEmail'),
                               'advisor'   => array('IsNumeric'));

   /**
    * Changes data of a user account.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/UpdateUser")
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
               $oldUserType = $user->get('Type');
               
               $sqlm = \rocinante\persistence\SqlManager::instance();
               $user->set('Username', $sqlm->escape($this->request->getProperty('username')['value']));
               if (!empty($password))
               {
                  $user->set('Password', \password_hash($sqlm->escape($this->request->getProperty('password')['value']), \PASSWORD_DEFAULT));
               }
               $user->set('Type', $sqlm->escape($this->request->getProperty('type')['value']));
               $user->set('FirstName', $sqlm->escape($this->request->getProperty('name')['value']));
               $user->set('Gender', $sqlm->escape($this->request->getProperty('gender')['value']));
               $user->set('Email', $sqlm->escape($this->request->getProperty('email')['value']));
               $assembler->update($user);
               
               $this->updateAdvisor($user, $oldUserType, $sqlm->escape($this->request->getProperty('advisor')['value']));
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = \sprintf((string) $l10n->{"dialog"}->{"user"}->{"success-modification"}, $username);
         }
         else
         {
            $array["result"] = "null";
            $array["html"] = $l10n->{"validation"}->{"error"} . "<br />" . $message;
         }
         echo \json_encode($array);
      }
   }
   
   /**
    * Updates who is this user's advisor, or removes the advisor if type of user is not TRANSLATOR 
    * anymore.
    * @param rocinante\domain\model\User $user A user.
    * @param string $oldUserType Type of user before updating.
    * @param int $advisorid A user ID that identifies an advisor for the user.
    */
   private function updateAdvisor($user, $oldUserType, $advisorid)
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Pupil");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      
      if ($oldUserType === "TRANSLATOR")
      {
         $identity = $factory->getIdentity();
         $identity->field("PupilId")->eq($user->get('UserId'));
         $collection = $assembler->find($identity);
         $pupil = $collection->first();
         if ($pupil === null)
         {
            throw new \Exception("Pupil not found");
         }
         
         // If the user continues to be a translator and he/she has a new one, update it.
         if ($user->get('Type') === "TRANSLATOR")
         {
            if ($pupil->get('AdvisorId') !== $advisorid)
            {
               $pupil->set('AdvisorId', $advisorid);
               $assembler->update($pupil);
            }
         }
         // If the user is not a translator anymore, delete his/her advisor.
         else
         {
            $assembler->delete($identity);
         }
      }
      // If the user was not a translator and he/she is now, assign the advisor.
      else if ($user->get('Type') === "TRANSLATOR")
      {
         $newPupil = $factory->getDomainFactory()->createObject(array());
         $newPupil->set('AdvisorId', $advisorid);
         $newPupil->set('PupilId', $user->get('UserId'));
         $assembler->insert($newPupil);
      }
   }
}
