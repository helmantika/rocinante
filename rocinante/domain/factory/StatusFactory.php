<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Status.php';

/**
 * StatusFactory creates Status domain objects from raw data.
 */
class StatusFactory implements DomainFactory
{

   /**
    * Creates a Status domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Status A Status object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Status();
      $object->populate($array);
      return $object;
   }

}
