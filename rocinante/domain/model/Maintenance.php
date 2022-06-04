<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Maintenance is a domain class that represents the table that stores whether Rocinante is being
 * maintenanced.
 */
class Maintenance extends Domain
{

   /**
    * Creates a new Maintenance object.
    */
   public function __construct()
   {
      $fields = array('MaintenanceId' => 'i',
                      'Active' => 'i',
                      'Message' => 's');
      parent::__construct($fields);
   }

}
