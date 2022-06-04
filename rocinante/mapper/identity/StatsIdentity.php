<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * StatsIdentity encapsulates the conditional aspect of a database query that selects information 
 * from Stats database table.
 */
class StatsIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('UserId' => 'i',
                                 'Translated' => 'i',
                                 'Revised' => 'i',
                                 'Updated' => 'i',
                                 'Last' => 's'), "Stats", $field);
   }

}
