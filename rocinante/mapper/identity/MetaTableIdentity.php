<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * MetaTableIdentity encapsulates the conditional aspect of a database query that selects 
 * information from MetaTable database table.
 */
class MetaTableIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('MetaTableId' => 'i',
                                 'Seq' => 'i',
                                 'TableId' => 'i'), "MetaTable", $field);
   }

}
