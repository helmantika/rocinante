<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * LangTypeIdentity encapsulates the conditional aspect of a database query that selects information 
 * from LangType database table.
 */
class LangTypeIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('TypeId' => 'i',
                                 'Description' => 's',
                                 'Color' => 's'), "LangType", $field);
   }

}
