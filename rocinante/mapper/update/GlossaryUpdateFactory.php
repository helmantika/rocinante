<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * GlossaryUpdateFactory acquires the infomation necessary to build prepared UPDATE or INSERT 
 * statements for a Glossary object.
 */
class GlossaryUpdateFactory extends UpdateFactory
{

   /**
    * Updates a Glossary object if its ID is set, or inserts a Glossary object whether it's not.
    * @param \rocinante\domain\Domain $object A Glossary object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values for WHERE clause in an UPDATE statement, and they are values to add in
    * a INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      $termid = $object->get('TermId');
      $condition = null;
      if ($termid !== null)
      {
         $condition['TermId'] = $termid;
      }
      return $this->buildStatement("Glossary", $object->fields(), $object->types(), $condition);
   }

}
