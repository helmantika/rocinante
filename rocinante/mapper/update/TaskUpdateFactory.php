<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * TaskUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT statements
 * for a Task object.
 */
class TaskUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Task object if its ID is set, or inserts a Task object whether it's not.
    * @param \rocinante\domain\Domain $object A Task object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $taskid = $object->get('TaskId');
      $condition = null;
      if ($taskid !== null)
      {
         $condition['TaskId'] = $taskid;
      }
      return $this->buildStatement("Task", $object->fields(), $object->types(), $condition);
   }

}
