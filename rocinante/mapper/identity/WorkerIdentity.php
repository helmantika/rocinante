<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * WorkerIdentity encapsulates the conditional aspect of a database query that selects information
 * from Worker database table.
 */
class WorkerIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('TableId' => 'i',
                                 'UserId' => 'i',
                                 'IsTranslating' => 'i',
                                 'IsRevising' => 'i'), "Worker", $field);
   }

}
