<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * LangSearch is a domain class that represents a result set of searching for game and translation
 * strings.
 */
class LangSearch extends Domain
{

   /**
    * Creates a new searching result.
    */
   public function __construct()
   {
      $fields = array('TableId' => 'i',
                      'TextId' => 'i',
                      'SeqId' => 'i',
                      'Fr' => 's',
                      'En' => 's',
                      'Es' => 's');
      parent::__construct($fields);
   }

}
