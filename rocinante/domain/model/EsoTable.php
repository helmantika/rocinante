<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * EsoTable is a domain class that represents a table of The Elder Scrolls Online that contains game
 * strings to translate.
 */
class EsoTable extends Domain
{

   /**
    * Creates a new master table.
    */
   public function __construct()
   {
      $fields = array('TableId' => 'i',
                      'Number' => 'i',
                      'Description' => 's',
                      'TypeId' => 'i',
                      'Size' => 'i',
                      'Translated' => 'd',
                      'Revised' => 'd',
                      'New' => 'i',
                      'Modified' => 'i');
      parent::__construct($fields);
   }

}
