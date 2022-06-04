<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * MaintenanceUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a Maintenance object.
 */
class MaintenanceUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Status object if its ID is set, or inserts a user object if it's not.
    * @param \rocinante\domain\Domain $object A Maintenance object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $maintenanceid = $object->get('MaintenanceId');
      $condition = null;
      if ($maintenanceid !== null)
      {
         $condition['MaintenanceId'] = $maintenanceid;
      }
      return $this->buildStatement("Maintenance", $object->fields(), $object->types(), $condition);
   }

}
