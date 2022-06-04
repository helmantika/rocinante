<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/EsoTable.php';

/**
 * EsoTableFactory creates EsoTable domain objects from raw data.
 */
class EsoTableFactory implements DomainFactory
{

   /**
    * Creates an EsoTable domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\EsoTable An EsoTable object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\EsoTable();
      $object->populate($array);
      return $object;
   }

}
