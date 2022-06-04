<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/LangSearch.php';

/**
 * LangSearchFactory creates LangSearch domain objects from raw data.
 */
class LangSearchFactory implements DomainFactory
{

   /**
    * Creates a LangSearch domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\LangSearch A LangSearch object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\LangSearch();
      $object->populate($array);
      return $object;
   }

}