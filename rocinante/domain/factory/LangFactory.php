<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Lang.php';

/**
 * LangFactory creates Lang domain objects from raw data.
 */
class LangFactory implements DomainFactory
{

   /**
    * Creates a Lang domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Lang A Lang object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Lang();
      $object->populate($array);
      return $object;
   }

}