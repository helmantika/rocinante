<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * LangIdentity encapsulates the conditional aspect of a database query that selects information 
 * from Lang database table.
 */
class LangIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('TableId' => 'i',
                                 'TextId' => 'i',
                                 'SeqId' => 'i',
                                 'Fr' => 's',
                                 'En' => 's',
                                 'Es' => 's',
                                 'Notes' => 's',
                                 'IsAssigned' => 'i',
                                 'IsTranslated' => 'i',
                                 'IsRevised' => 'i',
                                 'IsLocked' => 'i',
                                 'IsDisputed' => 'i',
                                 'IsNew' => 'i',
                                 'IsModified' => 'i',
                                 'IsDeleted' => 'i'), "Lang", $field);
   }

}
