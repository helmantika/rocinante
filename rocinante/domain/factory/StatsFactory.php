<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Stats.php';

/**
 * StatsFactory creates Stats domain objects from raw data.
 */
class StatsFactory implements DomainFactory
{

   /**
    * Creates a Stats domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Stats A Stats object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Stats();
      $object->populate($array);
      return $object;
   }

}
