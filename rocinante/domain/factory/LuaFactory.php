<?php

namespace rocinante\domain\factory;

require_once 'rocinante/domain/factory/DomainFactory.php';
require_once 'rocinante/domain/model/Lua.php';

/**
 * LuaFactory creates Lua domain objects from raw data.
 */
class LuaFactory implements DomainFactory
{

   /**
    * Creates a Lua domain object.
    * @param array $array A raw data array where keys are field names and values are field values.
    * @return \rocinante\domain\model\Lua A Lua object.
    */
   public function createObject(array $array)
   {
      $object = new \rocinante\domain\model\Lua();
      $object->populate($array);
      return $object;
   }

}
