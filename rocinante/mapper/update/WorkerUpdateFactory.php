<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * WorkerUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a Worker object.
 */
class WorkerUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Worker object. It's mandatory to set fields that are the primary key.
    * @param \rocinante\domain\Domain $object A Worker object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $tableid = $object->get('TableId');
      $userid = $object->get('UserId');
      $condition = null;
      if ($tableid !== null && $userid !== null)
      {
         $condition['TableId'] = $tableid;
         $condition['UserId'] = $userid;
      }
      return $this->buildStatement("Worker", $object->fields(), $object->types(), $condition);
   }

   /**
    * Inserts a new Worker object.
    * @param \rocinante\domain\Domain $object A Worker object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add to an INSERT statement.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("Worker", $object->fields(), $object->types());
   }
   
}
