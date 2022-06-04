<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/domain/model/Glossary.php';
require_once 'rocinante/command/task/InsertGlossaryTask.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * UpdateTerm updates a glossary term.
 */
class UpdateTerm extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('termid'      => array('IsNumeric'),
                               'term'        => array('IsNonEmpty'),
                               'translation' => array('IsNonEmpty'),
                               'typeid'      => array('IsNumeric'),
                               'locked'      => array('IsNumeric'));
   
   /**
    * Updates a glossary term.
    */
   public function doExecute()
   {
      $result = false;
      if ($this->request->getProperty('cmd') === "translation/UpdateTerm")
      {
         // Resume the current session.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $factory = new \rocinante\persistence\PersistenceFactory("Glossary");
            $assembler = new \rocinante\persistence\DomainAssembler($factory);
            $identity = $factory->getIdentity();
            $termid = $sqlm->escape($this->request->getProperty('termid')['value']);
            $identity->field("TermId")->eq($termid);
            $collection = $assembler->find($identity);
            $object = $collection->first();
            if ($object !== null )
            {
               $translation = $sqlm->escape($this->request->getProperty('translation')['value']);
               $createTask = $translation !== $object->get('Translation');
               $object->set('Translation', $translation);
               $typeid = \intval($this->request->getProperty('typeid')['value']);
               $object->set('TypeId', $typeid);
               $note = $this->request->getProperty('note')['value'];
               if (!empty($note))
               {
                  $object->set('Note', $note);
               }
               $isLocked = \intval($this->request->getProperty('locked')['value']);
               $object->set('IsLocked', $isLocked);
               $result = $assembler->update($object);
               
               // Create a task to update strings that contain the modified term.
               if ($result && $createTask)
               {
                  $term = $sqlm->escape($this->request->getProperty('term')['value']);
                  $task = new \rocinante\command\task\InsertGlossaryTask($term, $this->request);
                  $task->execute();
               }
               
               // Process plural form.
               if ($result)
               {
                  $pluralTerm = $sqlm->escape($this->request->getProperty('plural')['value']);
                  if (!empty($pluralTerm))
                  {
                     // Search plural term of term ID.
                     $identity = $factory->getIdentity();
                     $identity->field("SingularId")->eq($termid);
                     $collection = $assembler->find($identity);
                     $plural = $collection->first();
                     if ($plural !== null )
                     {
                        // Update translation for plural.
                        $plural->set('Translation', $translation);
                        $plural->set('TypeId', $typeid);
                        $plural->set('IsLocked', $isLocked);
                        $result = $assembler->update($plural);

                        // Create a task to update strings that contain the plural term.
                        if ($result && $createTask)
                        {
                           $task = new \rocinante\command\task\InsertGlossaryTask($pluralTerm, $this->request);
                           $task->execute();
                        }
                     }
                     else
                     {
                        // Add plural form for term.
                        $glossary = new \rocinante\domain\model\Glossary();
                        $glossary->set('Term', $pluralTerm);
                        $glossary->set('Translation', $translation);
                        $glossary->set('TypeId', $typeid);
                        $glossary->set('SingularId', $termid);

                        $result = $assembler->update($glossary);
                     }
                  }
               }
            }
         }
      }
      
      $array["result"] = $result ? "ok" : "null";
      echo \json_encode($array);
   }
}
