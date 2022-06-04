<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * TaskContentsUpdateFactory acquires the infomation necessary to build prepared INSERT statements 
 * for a TaskContents object. This type of object can't be updated.
 */
class TaskContentsUpdateFactory extends UpdateFactory
{

   /**
    * Updates a TaskContents object. It's mandatory to set fields that are the primary key.
    * @param \rocinante\domain\Domain $object A TaskContents object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $taskid = $object->get('TaskId');
      $tableid = $object->get('TableId');
      $textid = $object->get('TextId');
      $luaTextid = $object->get('LuaTextId');
      $seqid = $object->get('SeqId');
      $condition = null;
      if ($taskid !== null && $tableid !== null && $textid !== null && $luaTextid !== null && $seqid !== null)
      {
         $condition['TaskId'] = $taskid;
         $condition['TableId'] = $tableid;
         $condition['TextId'] = $textid;
         $condition['LuaTextId'] = $luaTextid;
         $condition['SeqId'] = $seqid;
      }
      return $this->buildStatement("TaskContents", $object->fields(), $object->types(), $condition);
   }
   
   /**
    * Inserts a TaskContents object.
    * @param \rocinante\domain\Domain $object A TaskContents object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add in a INSERT statement.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("TaskContents", $object->fields(), $object->types());
   }

}
