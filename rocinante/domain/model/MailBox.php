<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * MailBox is a domain class that represents a mailbox that belongs to a user.
 */
class MailBox extends Domain
{

   /**
    * Creates a new mailbox.
    */
   public function __construct()
   {
      $fields = array('MailId' => 'i',
                      'UserId' => 'i',
                      'Box' => 's',
                      'IsRead' => 'i');
      parent::__construct($fields);
   }

}
