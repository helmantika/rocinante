<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * MetaTableUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a MetaTable object.
 */
class MetaTableUpdateFactory extends UpdateFactory
{

   /**
    * Updates a MetaTable object if its ID is set, or inserts a Lang object whether it's not.
    * @param \rocinante\domain\Domain $object A MetaTable object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $metatableid = $object->get('MetaTableId');
      $tableid = $object->get('TableId');
      $condition = null;
      if ($metatableid !== null && $tableid !== null)
      {
         $condition['MetaTableId'] = $metatableid;
         $condition['TableId'] = $tableid;
      }
      return $this->buildStatement("MetaTable", $object->fields(), $object->types(), $condition);
   }
   
   /**
    * Inserts a new Lua object.
    * @param \rocinante\domain\Domain $object A MetaTable object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add to an INSERT statement.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("MetaTable", $object->fields(), $object->types());
   }

}
