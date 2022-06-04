<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/MailBox.php';

/**
 * MailBoxFactory creates MailBox domain objects from raw data.
 */
class MailBoxFactory implements DomainFactory
{

   /**
    * Creates a MailBox domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\MailBox A MailBox object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\MailBox();
      $object->populate($array);
      return $object;
   }

}
