<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/TermType.php';

/**
 * TermTypeFactory creates TermType domain objects from raw data.
 */
class TermTypeFactory implements DomainFactory
{

   /**
    * Creates a TermType domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Task A TermType object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\TermType();
      $object->populate($array);
      return $object;
   }

}
