<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Lang is a domain class that represents a translation unit extracted from The Elder Scrolls Online
 * en.lang binary file. Basically a translation unit is a string that must be translated. A string
 * can be from a simple word to a lore book (hundreds of words).
 */
class Lang extends Domain
{

   /**
    * Creates a new translation unit.
    */
   public function __construct()
   {
      $fields = array('TableId' => 'i',
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
                      'IsDeleted' => 'i');
      parent::__construct($fields);
   }

}
