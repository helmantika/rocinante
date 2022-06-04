<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * MailBoxIdentity encapsulates the conditional aspect of a database query that selects information 
 * from MailBox database table.
 */
class MailBoxIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('MailId' => 'i',
                                 'UserId' => 'i',
                                 'Box' => 's',
                                 'IsRead' => 'i'), "MailBox", $field);
   }

}
