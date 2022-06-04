<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/User.php';

/**
 * UserFactory creates User domain objects from raw data.
 */
class UserFactory implements DomainFactory
{

   /**
    * Creates a User domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\User A User object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\User();
      $object->populate($array);
      return $object;
   }

}
