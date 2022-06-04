<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * MaintenanceIdentity encapsulates the conditional aspect of a database query that selects 
 * information from Maintenance database table.
 */
class MaintenanceIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('MaintenanceId' => 'i',
                                 'Active' => 'i',
                                 'Message' => 's'), "Maintenance", $field);
   }

}
