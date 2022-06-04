<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/TaskContents.php';

/**
 * TaskContentsFactory creates TaskContents domain objects from raw data.
 */
class TaskContentsFactory implements DomainFactory
{

   /**
    * Creates a TaskContents domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\TaskContents A TaskContents object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\TaskContents();
      $object->populate($array);
      return $object;
   }

}
