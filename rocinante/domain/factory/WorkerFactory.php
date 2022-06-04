<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Worker.php';

/**
 * WorkerFactory creates Worker domain objects from raw data.
 */
class WorkerFactory implements DomainFactory
{

   /**
    * Creates a Worker domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Worker A Worker object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Worker();
      $object->populate($array);
      return $object;
   }

}
