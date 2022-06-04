<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * EsoTableIdentity encapsulates the conditional aspect of a database query that selects information
 * from EsoTable database table.
 */
class EsoTableIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('TableId' => 'i',
                                 'Number' => 'i',
                                 'Description' => 's',
                                 'TypeId' => 'i',
                                 'Size' => 'i',
                                 'Translated' => 'd',
                                 'Revised' => 'd',
                                 'New' => 'i',
                                 'Modified' => 'i'), "EsoTable", $field);
   }

}
