<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * SelectEsoTable selects description and type of an ESO table.
 */
class SelectEsoTable extends \rocinante\controller\Command
{
   
   /**
    * Retrieves terms of the glossary that are included in a given English string.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/SelectEsoTable")
      {
         $description = null;
         $type = 0;
         $types = array();

         $tableid = $this->request->getProperty('tableid');

         // Select description and type.
         $esoTablefactory = new \rocinante\persistence\PersistenceFactory("EsoTable");
         $assemblerEsoTableFactory = new \rocinante\persistence\DomainAssembler($esoTablefactory);
         $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TypeId' => 'i', 'Description' => 's'), "EsoTable");
         $esoTableIdentity->field("TableId")->eq($tableid);         
         $esoTableCollection = $assemblerEsoTableFactory->find($esoTableIdentity);
         $object = $esoTableCollection->first();
         if ($object !== null )
         {
            $description = $object->get('Description');
            $type = $object->get('TypeId');
         }        
         
         // Get every table type description. 
         $langTypeFactory = new \rocinante\persistence\PersistenceFactory("LangType");
         $assemblerLangTypeFactory = new \rocinante\persistence\DomainAssembler($langTypeFactory);
         $langTypeIdentity = new \rocinante\mapper\identity\Identity(array('TypeId' => 'i', 'Description' => 's'), "LangType");         
         $langTypeIdentity->orderByAsc('TypeId');
         $langTypeCollection = $assemblerLangTypeFactory->find($langTypeIdentity);
         $generator = $langTypeCollection->getGenerator();
         foreach ($generator as $langType)
         {
            $types[\intval($langType->get('TypeId'))] = $langType->get('Description');
         }
         
         echo \json_encode(array("description" => $description, "type" => $type, "types" => $types));
      }
   }
}
