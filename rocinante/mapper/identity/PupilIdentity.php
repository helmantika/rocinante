<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * PupilIdentity encapsulates the conditional aspect of a database query that selects information 
 * from Pupil database table.
 */
class PupilIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('RelationId' => 'i',
                                 'AdvisorId' => 'i',
                                 'PupilId' => 'i'), "Pupil", $field);
   }

}
