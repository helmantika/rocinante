<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * LangUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT statements
 * for a Lang object.
 */
class LangUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Lang object if its ID is set, or inserts a Lang object whether it's not.
    * @param \rocinante\domain\Domain $object A Lang object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $tableid = $object->get('TableId');
      $textid = $object->get('TextId');
      $seqid = $object->get('SeqId');
      $condition = null;
      if ($tableid !== null && $textid !== null && $seqid !== null)
      {
         $condition['TableId'] = $tableid;
         $condition['TextId'] = $textid;
         $condition['SeqId'] = $seqid;
      }
      return $this->buildStatement("Lang", $object->fields(), $object->types(), $condition);
   }

   /**
    * Inserts a new Lang object.
    * @param \rocinante\domain\Domain $object A Lang object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add to an INSERT statement.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("Lang", $object->fields(), $object->types());
   }
}
