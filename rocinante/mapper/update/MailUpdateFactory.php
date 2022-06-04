<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * MailUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a Mail object.
 */
class MailUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Mail object. It's mandatory to set fields that are the primary key.
    * @param \rocinante\domain\Domain $object A Mail object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $mailid = $object->get('MailId');
      $condition = null;
      if ($mailid !== null)
      {
         $condition['MailId'] = $mailid;
      }
      return $this->buildStatement("Mail", $object->fields(), $object->types(), $condition);
   }

   /**
    * Inserts a new Mail object.
    * @param \rocinante\domain\Domain $object A Mail object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add to an INSERT statement.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("Mail", $object->fields(), $object->types());
   }
   
}
