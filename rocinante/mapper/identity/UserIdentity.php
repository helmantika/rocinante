<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UserIdentity encapsulates the conditional aspect of a database query that selects information
 * from User database table.
 */
class UserIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('UserId' => 'i',
                                 'Username' => 's',
                                 'FirstName' => 's',
                                 'Gender' => 's',
                                 'Email' => 's',
                                 'Password' => 's',
                                 'SessionId' => 's',
                                 'Type' => 's',
                                 'Theme' => 's',
                                 'Since' => 's',
                                 'IsActive' => 'i'), "User", $field);
   }

}
