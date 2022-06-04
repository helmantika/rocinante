<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Status is a domain class that represents the table that stores how many string are translated,
 * ow many strings must be translated, and their ratio (percentage).
 */
class Status extends Domain
{

   /**
    * Creates a new translation status.
    */
   public function __construct()
   {
      $fields = array('StatusId' => 'i',
                      'Translated' => 'i',
                      'Total' => 'i',
                      'Percentage' => 'd');
      parent::__construct($fields);
   }

}
