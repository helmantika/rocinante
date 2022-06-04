<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/domain/model/Glossary.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * InsertTerm adds a new term to the glossary.
 *
 * @author jorge
 */
class InsertTerm extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('term'        => array('IsNonEmpty'),
                               'translation' => array('IsNonEmpty'),
                               'typeid'      => array('IsNumeric'));
   
   /**
    * Inserts a new term to the glossary.
    */
   public function doExecute()
   {
      $result = false;
      if ($this->request->getProperty('cmd') === "translation/InsertTerm")
      {
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $term = $sqlm->escape($this->request->getProperty('term')['value']);
            $plural = $sqlm->escape($this->request->getProperty('plural')['value']);
            $typeid = \intval($this->request->getProperty('typeid')['value']);
            
            // Check that term is not in glossary yet.
            $factory = new \rocinante\persistence\PersistenceFactory("Glossary");
            $assembler = new \rocinante\persistence\DomainAssembler($factory);
            $identity = $factory->getIdentity();
            $identity->field("Term")->eq($term)->iand()->field("TypeId")->eq($typeid);
            $collection = $assembler->find($identity);
            $object = $collection->first();
            if ($object === null )
            {
               $glossary = new \rocinante\domain\model\Glossary();
               $glossary->set('Term', \trim($term));
               $glossary->set('Translation', \trim($sqlm->escape($this->request->getProperty('translation')['value'])));
               $glossary->set('TypeId', $typeid);
               $note = $this->request->getProperty('note')['value'];
               if (!empty($note))
               {
                  $glossary->set('Note', $note);
               }
               $glossary->set('IsLocked', 0);

               $result = $assembler->update($glossary);
               $termid = $glossary->get('TermId');
               
               // If there is a plural term, add it as a new one.
               if ($result && !empty($plural))
               {
                  $termid = $glossary->get('TermId');
                  $glossary = new \rocinante\domain\model\Glossary();
                  $glossary->set('Term', $plural);
                  $glossary->set('Translation', $sqlm->escape($this->request->getProperty('translation')['value']));
                  $glossary->set('TypeId', $typeid);
                  $glossary->set('SingularId', $termid);
                  
                  $result = $assembler->update($glossary);
               }
            }
            else
            {
               $viewhelper = \rocinante\view\ViewHelper::instance();
               $l10n = $viewhelper->getL10n();
               $message = (string) $l10n->{"dialog"}->{"glossary"}->{"term-exists"};
            }
         }
      }
      
      $array["result"] = $result ? "ok" : "null";
      $array["html"] = $message;
      echo \json_encode($array);
   }
}
