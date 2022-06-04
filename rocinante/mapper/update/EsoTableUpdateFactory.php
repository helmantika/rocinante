<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * EsoTableUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for an EsoTable object.
 */
class EsoTableUpdateFactory extends UpdateFactory
{

   /**
    * Updates an EsoTable object whether its ID is set, or inserts a EsoTable object whether it's not.
    * @param \rocinante\domain\Domain $object An EsoTable object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $userid = $object->get('TableId');
      $condition = null;
      if ($userid !== null)
      {
         $condition['TableId'] = $userid;
      }
      return $this->buildStatement("EsoTable", $object->fields(), $object->types(), $condition);
   }
   
   /**
    * Inserts a new EsoTable object.
    * @param \rocinante\domain\Domain $object An EsoTable object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add to an INSERT statement.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("EsoTable", $object->fields(), $object->types());
   }

}
