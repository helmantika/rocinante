<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Jumble.php';

/**
 * JumbleFactory creates Jumble domain objects from raw data.
 */
class JumbleFactory implements DomainFactory
{

   /**
    * Creates a Jumble domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Jumble A Jumble object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Jumble();
      $object->populate($array);
      return $object;
   }

}