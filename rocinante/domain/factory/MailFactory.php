<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Mail.php';

/**
 * MailFactory creates Mail domain objects from raw data.
 */
class MailFactory implements DomainFactory
{

   /**
    * Creates a Mail domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Mail A Mail object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Mail();
      $object->populate($array);
      return $object;
   }

}
