<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * TermTypeIdentity encapsulates the conditional aspect of a database query that selects information 
 * from TermType database table.
 */
class TermTypeIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('TypeId' => 's',
                                 'Description' => 's'), "TermType", $field);
   }

}
