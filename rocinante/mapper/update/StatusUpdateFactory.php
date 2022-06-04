<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * StatusUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a Status object.
 */
class StatusUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Status object if its ID is set, or inserts a user object if it's not.
    * @param \rocinante\domain\Domain $object A Status object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $statusid = $object->get('StatusId');
      $condition = null;
      if ($statusid !== null)
      {
         $condition['StatusId'] = $statusid;
      }
      return $this->buildStatement("Status", $object->fields(), $object->types(), $condition);
   }

}
