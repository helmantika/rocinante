<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * LuaUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT statements
 * for a Lua object.
 */
class LuaUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Lua object if its ID is set, or inserts a Lang object whether it's not.
    * @param \rocinante\domain\Domain $object A Lang object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $textid = $object->get('TextId');
      $condition = null;
      if ($textid !== null)
      {
         $condition['TextId'] = $textid;
      }
      return $this->buildStatement("Lua", $object->fields(), $object->types(), $condition);
   }
   
   /**
    * Inserts a new Lua object.
    * @param \rocinante\domain\Domain $object A Lua object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add to an INSERT statement.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("Lua", $object->fields(), $object->types());
   }

}
