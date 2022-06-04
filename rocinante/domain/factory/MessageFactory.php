<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Message.php';

/**
 * MessageFactory creates Message domain objects from raw data.
 */
class MessageFactory implements DomainFactory
{

   /**
    * Creates a Message domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Message A Message object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Message();
      $object->populate($array);
      return $object;
   }

}
