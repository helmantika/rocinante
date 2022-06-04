<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Pupil.php';

/**
 * PupilFactory creates Pupil domain objects from raw data.
 */
class PupilFactory implements DomainFactory
{

   /**
    * Creates a Pupil domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Pupil A Pupil object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Pupil();
      $object->populate($array);
      return $object;
   }

}
