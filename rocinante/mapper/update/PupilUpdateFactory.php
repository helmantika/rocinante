<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * PupilUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a Pupil object.
 */
class PupilUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Pupil object whether its ID is set, or inserts a Pupil object whether it's not.
    * @param \rocinante\domain\Domain $object A Pupil object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $relationid = $object->get('RelationId');
      $condition = null;
      if ($relationid !== null)
      {
         $condition['RelationId'] = $relationid;
      }
      return $this->buildStatement("Pupil", $object->fields(), $object->types(), $condition);
   }

}
