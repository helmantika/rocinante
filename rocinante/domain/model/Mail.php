<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Mail is a domain class that relates message senders and addressees.
 */
class Mail extends Domain
{

   /**
    * Creates a new message.
    */
   public function __construct()
   {
      $fields = array('MailId' => 'i',
                      'SenderId' => 'i',
                      'AddresseeId' => 'i',
                      'ChatId' => 'i');
      parent::__construct($fields);
   }

}
