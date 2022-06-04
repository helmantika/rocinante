<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Lua is a domain class that represents a translation unit extracted from The Elder Scrolls Online
 * en_pregame.lua and en_client.lua files. Basically a translation unit is a string that must be
 * translated. LUA file strings are related to the user interface.
 */
class Lua extends Domain
{

   /**
    * Creates a new translation unit.
    */
   public function __construct()
   {
      $fields = array('TableId' => 'i',
                      'TextId' => 's',
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
