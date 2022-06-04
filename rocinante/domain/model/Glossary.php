<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Glossary is a domain class that represents a translation glossary. The glossary contains key
 * terminology (terms) of the game in English and approved translations for that terminology in the
 * target language.
 */
class Glossary extends Domain
{

   /**
    * Creates a new term.
    * @param int $termid A univocal identifier for the term or null whether it is a new term.
    */
   public function __construct($termid = null)
   {
      $fields = array('TermId' => 'i',
                      'Term' => 's',
                      'Translation' => 's',
                      'Note' => 's',
                      'TypeId' => 'i',
                      'IsLocked' =>'i',
                      'SingularId' => 'i');
      parent::__construct($fields);
      if ($termid !== null)
      {
         parent::set('TermId', $termid);
      }
   }

   /**
    * Sets a univocal ID for this object generated by the database because the ID field has the AUTO
    * INCREMENT attribute.
    */
   public function setLastInsertId($value)
   {
      if ($this->get('TermId') === null)
      {
         $this->set('TermId', $value);
      }
   }

}