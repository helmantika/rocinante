<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * SelectAdvisors retrieves a list of users who are advisors.
 */
class SelectAdvisors extends \rocinante\controller\Command
{

   /**
    * Retrieves a list of users who are advisors.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/SelectAdvisors")
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
            $advisors[\intval($object->get('UserId'))] = $object->get('Username');
         }

         echo \json_encode($advisors);
      }
   }

}
