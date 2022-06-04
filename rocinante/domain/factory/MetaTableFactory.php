<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/MetaTable.php';

/**
 * MetaTableFactory creates MetaTable domain objects from raw data.
 */
class MetaTableFactory implements DomainFactory
{

   /**
    * Creates a MetaTable domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\MetaTable A MetaTable object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\MetaTable();
      $object->populate($array);
      return $object;
   }

}
