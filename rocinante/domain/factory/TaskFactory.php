<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Task.php';

/**
 * TaskFactory creates Task domain objects from raw data.
 */
class TaskFactory implements DomainFactory
{

   /**
    * Creates a Task domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Task A Task object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Task();
      $object->populate($array);
      return $object;
   }

}
