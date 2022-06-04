<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/LangType.php';

/**
 * LangTypeFactory creates LangType domain objects from raw data.
 */
class LangTypeFactory implements DomainFactory
{

   /**
    * Creates a LangType domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\LangType A LangType object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\LangType();
      $object->populate($array);
      return $object;
   }

}
