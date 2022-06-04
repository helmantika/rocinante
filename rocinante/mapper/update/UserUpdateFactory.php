<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * UserUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT statements
 * for a User object.
 */
class UserUpdateFactory extends UpdateFactory
{

   /**
    * Updates a User object whether its ID is set, or inserts a user object whether it's not.
    * @param \rocinante\domain\Domain $object A User object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $userid = $object->get('UserId');
      $condition = null;
      if ($userid !== null)
      {
         $condition['UserId'] = $userid;
      }
      return $this->buildStatement("User", $object->fields(), $object->types(), $condition);
   }

}
