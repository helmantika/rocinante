<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * LangGlossary is a domain class that binds a translation unit and glossary terms that are found in
 * that unit.
 */
class LangGlossary extends Domain
{

   /**
    * Creates a new relationship between a string and glossary terms.
    */
   public function __construct()
   {
      $fields = array('TableId' => 'i',
                      'TextId' => 'i',
                      'SeqId' => 'i',
                      'TermId' => 'i');
      parent::__construct($fields);
   }

}
