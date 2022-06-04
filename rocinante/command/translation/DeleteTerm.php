<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * DeleteTerm removes a glossary term.
 */
class DeleteTerm extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('termid' => array('IsNumeric'));
   
   /**
    * The Glossary factory.
    * @var \rocinante\persistence\PersistenceFactory
    */
   private $glossaryFactory = null;

   /**
    * The Glossary object assembler.
    * @var \rocinante\persistence\DomainAssembler
    */
   private $glossaryAssembler = null;
   
   /**
    * The LangGlossary factory.
    * @var \rocinante\persistence\PersistenceFactory
    */
   private $langGlossaryFactory = null;

   /**
    * The LangGlossary object assembler.
    * @var \rocinante\persistence\DomainAssembler
    */
   private $langGlossaryAssembler = null;
   
   /**
    * Deletes a glossary term.
    */
   public function doExecute()
   {
      $result = false;
      if ($this->request->getProperty('cmd') === "translation/DeleteTerm")
      {
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $this->glossaryFactory = new \rocinante\persistence\PersistenceFactory("Glossary");
            $this->glossaryAssembler = new \rocinante\persistence\DomainAssembler($this->glossaryFactory);
            $identity = $this->glossaryFactory->getIdentity();
            $termid = $sqlm->escape($this->request->getProperty('termid')['value']);
            $identity->field("TermId")->eq($termid);
            $collection = $this->glossaryAssembler->find($identity);
            $object = $collection->first();
            if ($object !== null )
            {
               $this->glossaryAssembler->delete($identity);
               $result = true;
               
               // Delete those strings that have the term.
               $this->langGlossaryFactory = new \rocinante\persistence\PersistenceFactory("LangGlossary");
               $this->langGlossaryAssembler = new \rocinante\persistence\DomainAssembler($this->langGlossaryFactory);
               $langGlossaryIdentity = $this->langGlossaryFactory->getIdentity();
               $langGlossaryIdentity->field("TermId")->eq($termid);
               $this->langGlossaryAssembler->delete($langGlossaryIdentity);
               
               // Delete its plural term (if any)
               $this->deletePlural($termid);
            }
         }
      }
      
      $array["result"] = $result ? "ok" : "null";
      echo \json_encode($array);
   }
   
   /**
    * Deletes the plural form of a given glossary term.
    * @param int $termid A glossary term ID.
    */
   public function deletePlural($termid)
   {
      // Search plural term of term ID.
      $identity = $this->glossaryFactory->getIdentity();
      $identity->field("SingularId")->eq($termid);
      $collection = $this->glossaryAssembler->find($identity);
      $plural = $collection->first();
      if ($plural !== null )
      {
         $pluralid = $plural->get('TermId');
         $this->glossaryAssembler->delete($identity);
         
         // Delete those strings that have the term.
         $langGlossaryIdentity = $this->langGlossaryFactory->getIdentity();
         $langGlossaryIdentity->field("TermId")->eq($pluralid);
         $this->langGlossaryAssembler->delete($langGlossaryIdentity);
      }
   }
   
}
