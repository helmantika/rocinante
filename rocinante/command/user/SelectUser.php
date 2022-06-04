<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * SelectUser retrieves user data for a given username.
 */
class SelectUser extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request. 
    * @var array  
    */
   private $validation = array('username'  => array('IsNonEmpty', 'IsMaxLength(20)'));
   
   /**
    * Retrieves user data for a given username.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/SelectUser")
      {
         $user = null;
         
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);

         // Select the user.
         if (empty($message))
         {
            $username = $this->request->getProperty('username')['value'];
            $userFactory = new \rocinante\persistence\PersistenceFactory("User");
            $pupilFactory = new \rocinante\persistence\PersistenceFactory("Pupil");
            $userAssembler = new \rocinante\persistence\DomainAssembler($userFactory);
            $userIdentity = $userFactory->getIdentity();
            $pupilIdentity = $pupilFactory->getIdentity();
            $userIdentity->field("Username")->eq($username)->leftJoin($pupilIdentity, "UserId", "PupilId");
            $collection = $userAssembler->find($userIdentity);
            $object = $collection->first();
            if ($object !== null)
            {
               $user = array("userId" => $object->get('User.UserId'),
                             "username" => $object->get('User.Username'),
                             "firstName" => $object->get('User.FirstName'),
                             "gender" => $object->get('User.Gender'),
                             "email" => $object->get('User.Email'),
                             "type" => $object->get('User.Type'),
                             "advisor" => $object->get('Pupil.AdvisorId'),
                             "theme" => $object->get('User.Theme'));
            }
            
            $advisors = $this->selectAdvisors($object->get('User.UserId'));
            $themes = $this->loadThemes();
         }

         if ($user === null)
         {
            throw new Exception("User not found");
         }

         echo \json_encode(array("user" => $user, "advisors" => $advisors, "themes" => $themes));
      }
   }
   
   /**
    * Retrieves the list of users who are advisors.
    * @param int $userid Selected user ID.
    * @return Array An array with usernames where keys are the user ID.
    */
   private function selectAdvisors($userid)
   {
      $advisors = array();
      $factory = new \rocinante\persistence\PersistenceFactory("User");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $userIdentity = $factory->getIdentity();
      $userIdentity->field("Type")->neq("TRANSLATOR");
      $collection = $assembler->find($userIdentity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         if ($userid !== $object->get('UserId'))
         {
            $advisors[\intval($object->get('UserId'))] = $object->get('Username');
         }
      }
      return $advisors;
   }
   
   /**
    * Loads the theme file and gets the data related to every theme.
    * @throws \Exception Theme file was not found.
    * @return Array The name of all jQuery UI themes.
    */
   public function loadThemes()
   {
      if (!\file_exists("rocinante/view/themes.xml"))
      {
         throw new \Exception("Theme file was not found");
      }

      $result = array();
      $themes = \simplexml_load_file("rocinante/view/themes.xml");
      foreach ($themes as $theme)
      {
         $name = (string) $theme['name'];
         $result[$name] = $name;
      }
      
      return $result;
   }
}
