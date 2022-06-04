<?php

namespace rocinante\mapper\update;

require_once 'rocinante/mapper/update/UpdateFactory.php';

/**
 * LangGlossaryUpdate acquires the infomation necessary to build prepared INSERT statements for a 
 * LangGlossary object.
 */
class LangGlossaryUpdateFactory extends UpdateFactory
{

   /**
    * Inserts a LangGlossary object. LangGlossary objects can't be updated.
    * @param \rocinante\domain\Domain $object A LangGlossary object.
    * @return array An array whose first element is the statement and the second one is an array of
    * terms. Terms are values to add in an INSERT statement.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {
      return $this->buildStatement("LangGlossary", $object->fields(), $object->types());
   }

}
