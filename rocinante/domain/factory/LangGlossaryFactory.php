<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/LangGlossary.php';

/**
 * LangGlossaryFactory creates LangGlossary domain objects from raw data.
 */
class LangGlossaryFactory implements DomainFactory
{

   /**
    * Creates a LangGlossary domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\LangGlossary A LangGlossary object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\LangGlossary();
      $object->populate($array);
      return $object;
   }

}