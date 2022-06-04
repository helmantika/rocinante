<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * StatusIdentity encapsulates the conditional aspect of a database query that selects information 
 * from Status database table.
 */
class StatusIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('StatusId' => 'i',
                                 'Translated' => 'i',
                                 'Total' => 'i',
                                 'Percentage' => 'd'), "Status", $field);
   }

}
