<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * TaskIdentity encapsulates the conditional aspect of a database query that selects information 
 * from Task database table.
 */
class TaskIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct(array('TaskId' => 'i',
                                'TableId' => 'i',
                                'UserId' => 'i',
                                'AssignerId' => 'i',
                                'Date' => 's',
                                'Type' => 's',
                                'Term' => 's',
                                'Size' => 'i',
                                'Progress' => 'd'), "Task", $field);
   }

}
