<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * SelectLangGlossary selects terms of the glossary that are included in a given English string.
 */
class SelectLangGlossary extends \rocinante\controller\Command
{
   
   /**
    * The Glossary factory.
    * @var \rocinante\persistence\PersistenceFactory
    */
   private $factory = null;

   /**
    * The Glossary object assembler.
    * @var \rocinante\persistence\DomainAssembler
    */
   private $assembler = null;
   
   /**
    * Retrieves terms of the glossary that are included in a given English string.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/SelectLangGlossary")
      {
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $usertype = $session->getType();
         
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $terms = array();
         $locks = array();
         
         // CSS and abreviation for type of term.
         $css = array(1 => "noun-adjective-verb",
                      2 => "place-proper-name",
                      3 => "person-proper-name",
                      4 => "object-proper-name",
                      5 => "rank",
                      6 => "skill");
         
         $tableid = $this->request->getProperty('tableid');
         $textid = $this->request->getProperty('textid');
         $seqid = $this->request->getProperty('seqid');

         $this->factory = new \rocinante\persistence\PersistenceFactory("Glossary");
         $langGlossaryFactory = new \rocinante\persistence\PersistenceFactory("LangGlossary");
         $this->assembler = new \rocinante\persistence\DomainAssembler($this->factory);

         $langGlossaryIdentity = $langGlossaryFactory->getIdentity();
         $langGlossaryIdentity->field("TableId")->eq($tableid)->iand()->field("TextId")->eq($textid)->iand()->field("SeqId")->eq($seqid);
         $glossaryIdentity = $this->factory->getIdentity();
         $glossaryIdentity->join($langGlossaryIdentity, "TermId", "TermId");
         
         $collection = $this->assembler->find($glossaryIdentity);
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $this->replacePlural($object);            
            $note = str_replace("\'", "'", $object->get('Glossary.Note'));
            $note = str_replace('"', '&quot;', $object->get('Glossary.Note'));
            $row = array("termid" => $object->get('Glossary.TermId'),
                         "term" => str_replace("\'", "'", $object->get('Glossary.Term')),
                         "translation" => str_replace("\'", "'", $object->get('Glossary.Translation')),
                         "note" => (string) $l10n->{"dialog"}->{"glossary"}->{$css[$object->get('Glossary.TypeId')]} . (empty($note) ? "" : " || " . $note),
                         "css" => $css[$object->get('Glossary.TypeId')]);
            $terms[] = $row;
            
            // Translators can't change terms. If term is locked, advisors can't neither.
            if ($usertype === "TRANSLATOR" || ($usertype === "ADVISOR" && $object->get('Glossary.IsLocked')))
            {
               $locks[$object->get('Glossary.Term')] = true;
            }
         }
         
         // If there are not terms, inform about it.
         if (\count($terms) === 0)
         {
            $terms[] = array("term" => "!", 
                             "translation" => (string) $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"no-terms"},
                             "note" => "",
                             "css" => "");
            $locks["!"] = true;
         }
         
         // Add a special "term" to add new ones. Not for translators. 
         if ($usertype !== "TRANSLATOR")
         {
            $row = array("term" => "+", "translation" => (string) $l10n->{"dialog"}->{"glossary"}->{"add"});
            $terms[] = $row;
         }
         
         echo \json_encode(array("count" => \count($terms), "terms" => $terms, "locks" => $locks));
      }
   }
   
   /**
    * If the given term is a plural one, its singular term is searched and plural term data are 
    * replaced with singular term data.
    * @param string $plural A term from the glossary.
    */
   private function replacePlural(&$plural)
   {
      $identity = new \rocinante\mapper\identity\Identity(array('TermId' => 'i', 'SingularId' => 'i'), "Glossary");
      $identity->field("TermId")->eq($plural->get('Glossary.TermId'));
      $collection = $this->assembler->find($identity);
      $term = $collection->first();
      // If this term is a plural one, get data from the singular term.
      if ($term !== null && $term->get('SingularId') !== null)
      {
         $identity = $this->factory->getIdentity();
         $identity->field("TermId")->eq($term->get('SingularId'));
         $collection = $this->assembler->find($identity);
         $singular = $collection->first();
         if ($singular !== null)
         {
            $plural->set('Glossary.TermId', $singular->get('TermId'));
            $plural->set('Glossary.Term', $singular->get('Term'));
            $plural->set('Glossary.Translation', $singular->get('Translation'));
            $plural->set('Glossary.IsLocked', $singular->get('IsLocked'));
         }
      }
   }
   
}
