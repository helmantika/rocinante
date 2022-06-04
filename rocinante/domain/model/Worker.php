<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Worker is an associative domain class that connects an ESO table to an user who is translating
 * and/or revising it.
 */
class Worker extends Domain
{

   /**
    * Creates a new relationship between an ESO table and a user.
    */
   public function __construct()
   {
      $fields = array('TableId' => 'i',
                      'UserId' => 'i',
                      'IsTranslating' => 'i',
                      'IsRevising' => 'i');
      parent::__construct($fields);
   }

}
