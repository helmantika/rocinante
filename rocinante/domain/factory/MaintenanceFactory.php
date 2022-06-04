<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Maintenance.php';

/**
 * MaintenanceFactory creates Maintenance domain objects from raw data.
 */
class MaintenanceFactory implements DomainFactory
{

   /**
    * Creates a Maintenance domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Maintenance A Maintenance object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Maintenance();
      $object->populate($array);
      return $object;
   }

}
