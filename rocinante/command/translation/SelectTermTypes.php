<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * SelectTermTypes selects glossary term type descriptions.
 */
class SelectTermTypes extends \rocinante\controller\Command
{
   
   /**
    * Retrieves descriptions of glossary term types.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/SelectTermTypes")
      {
         $types = array();
         $factory = new \rocinante\persistence\PersistenceFactory("TermType");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $identity = $factory->getIdentity();
         $collection = $assembler->find($identity);
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $types[\intval($object->get('TypeId'))] = $object->get('Description');
         }        
         
         echo \json_encode($types);
      }
   }
}
