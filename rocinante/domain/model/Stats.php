<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Stats is a domain class that stores user statistics.
 */
class Stats extends Domain
{

   /**
    * Creates a new statistical record for a user.
    */
   public function __construct()
   {
      $fields = array('UserId' => 'i',
                      'Translated' => 'i',
                      'Revised' => 'i',
                      'Updated' => 'i',
                      'Last' => 's');
      parent::__construct($fields);
   }

}
