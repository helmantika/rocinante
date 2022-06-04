<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * LangUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT statements
 * for a Stats object.
 */
class StatsUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Stats object if its ID is set, or inserts a Stats object whether it's not.
    * @param \rocinante\domain\Domain $object A Stats object.
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
      return $this->buildStatement("Stats", $object->fields(), $object->types(), $condition);
   }

}
