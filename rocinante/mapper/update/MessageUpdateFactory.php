<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * MessageUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a Message object.
 */
class MessageUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Message object if its ID is set, or inserts a Message object whether it's not.
    * @param \rocinante\domain\Domain $object A Message object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $mailid = $object->get('MailId');
      $condition = null;
      if ($mailid !== null)
      {
         $condition['MailId'] = $mailid;
      }
      return $this->buildStatement("Message", $object->fields(), $object->types(), $condition);
   }

}
