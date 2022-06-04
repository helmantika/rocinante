<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * GlossaryIdentity encapsulates the conditional aspect of a database query that selects information
 * from Glossary database table.
 */
class GlossaryIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct(array('TermId' => 'i',
                                'Term' => 's',
                                'Translation' => 's',
                                'Note' => 's',
                                'TypeId' => 'i',
                                'IsLocked' =>'i',
                                'SingularId' => 'i'), "Glossary", $field);
   }

}
