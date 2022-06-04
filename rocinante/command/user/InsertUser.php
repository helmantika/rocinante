<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * InsertUser creates a new user account.
 */
class InsertUser extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request. 
    * @var array  
    */
   private $validation = array('username' => array('IsNonEmpty', 'IsMaxLength(20)'),
                               'password' => array('IsMinLength(6)'),
                               'type'     => array('IsUserType'),
                               'name'     => array('IsNonEmpty', 'IsMaxLength(30)'),
                               'gender'   => array('IsGender'),
                               'email'    => array('IsEmail'),
                               'advisor'  => array('IsNumeric'));

   /**
    * Creates a new user account.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/InsertUser")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $factory = new \rocinante\persistence\PersistenceFactory("User");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);

         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);

         // Check that passwords are equal.
         if ($this->request->getProperty('password')['value'] !== $this->request->getProperty('passwordv')['value'])
         {
            $message .= $l10n->{"validation"}->{"passwords-mismatch"} . "<br />";
         }

         // Check that username does not have any whitespace and it is not already selected.
         $username = null;
         if (empty($message))
         {
            $username = $this->request->getProperty('username')['value'];
            if (preg_match('/\s/',$username))
            {
               $message .= \sprintf($l10n->{"validation"}->{"username-has-whitespaces"} . "<br />", $username);
            }
            else
            {
               $userIdentity = new \rocinante\mapper\identity\Identity(array('Username' => 's'), "User");
               $userIdentity->field("Username")->eq($username);
               $collection = $assembler->find($userIdentity);
               $object = $collection->first();
               if ($object !== null)
               {
                  $message .= \sprintf($l10n->{"validation"}->{"username-exists"} . "<br />", $username);
               }
            }
         }

         // If everything is right, add the new user to the database.
         if (empty($message))
         {                           
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $newUser = $factory->getDomainFactory()->createObject(array());
            $newUser->set('Username', $sqlm->escape($this->request->getProperty('username')['value']));
            $newUser->set('Password', \password_hash($sqlm->escape($this->request->getProperty('password')['value']), \PASSWORD_DEFAULT));
            $newUser->set('Type', $sqlm->escape($this->request->getProperty('type')['value']));
            $newUser->set('FirstName', $sqlm->escape($this->request->getProperty('name')['value']));
            $newUser->set('Gender', $sqlm->escape($this->request->getProperty('gender')['value']));
            $newUser->set('Email', $sqlm->escape($this->request->getProperty('email')['value']));
            $newUser->set('Since', \date('Y-m-d'));
            $assembler->insert($newUser);
            
            // If the user is a translator then an advisor is assigned.
            if ($newUser->get('Type') === "TRANSLATOR")
            {
               $pupilFactory = new \rocinante\persistence\PersistenceFactory("Pupil");
               $pupilAssembler = new \rocinante\persistence\DomainAssembler($pupilFactory);
               $newPupil = $pupilFactory->getDomainFactory()->createObject(array());
               $newPupil->set('AdvisorId', $sqlm->escape($this->request->getProperty('advisor')['value']));
               $newPupil->set('PupilId', $newUser->get('UserId'));
               $pupilAssembler->insert($newPupil);
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = \sprintf((string) $l10n->{"dialog"}->{"user"}->{"success-addition"}, $username);
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
