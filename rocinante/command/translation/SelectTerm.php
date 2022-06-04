<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * SelectTerm selects a glossary term.
 */
class SelectTerm extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('termid' => array('IsNumeric'));
   
   /**
    * Retrieves all the data of a glossary term.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/SelectTerm")
      {
         $data = array();
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         
         // Retrieve term data by means of its ID.
         $termid = $this->request->getProperty('termid')['value'];
         if (empty($message))
         {
            $factory = new \rocinante\persistence\PersistenceFactory("Glossary");
            $assembler = new \rocinante\persistence\DomainAssembler($factory);
            $identity = $factory->getIdentity();
            $identity->field("TermId")->eq($termid);
            $collection = $assembler->find($identity);
            $object = $collection->first();
            if ($object !== null )
            {
               $termid = \intval($object->get('TermId'));
               // Search for its plural form.
               $identity = $factory->getIdentity();
               $identity->field("SingularId")->eq($termid);
               $collection = $assembler->find($identity);
               $plural = $collection->first();
               if ($plural !== null )
               {
                  $data['plural'] = str_replace("\'", "'", $plural->get('Term'));
               }
                  
               $data['termid'] = $termid;
               $data['term'] = str_replace("\'", "'", $object->get('Term'));
               $data['translation'] = str_replace("\'", "'", $object->get('Translation'));
               $data['typeid'] = \intval($object->get('TypeId'));
               $data['note'] = $object->get('Note');
               $data['locked'] = \intval($object->get('IsLocked'));
            }  
         }
      
         echo \json_encode($data);
      }
   }
}
