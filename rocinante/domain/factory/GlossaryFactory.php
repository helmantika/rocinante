<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Glossary.php';

/**
 * GlossaryFactory creates Glossary domain objects from raw data.
 */
class GlossaryFactory implements DomainFactory
{

   /**
    * Creates a Glossary domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Glossary A Glossary object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Glossary();
      $object->populate($array);
      return $object;
   }

}