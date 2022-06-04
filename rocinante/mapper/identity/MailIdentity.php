<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * MailIdentity encapsulates the conditional aspect of a database query that selects information 
 * from Mail database table.
 */
class MailIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('MailId' => 'i',
                                 'SenderId' => 'i',
                                 'AddresseeId' => 'i',
                                 'ChatId' => 'i'), "Mail", $field);
   }

}
