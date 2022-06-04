<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * DeleteMessage moves a message to 'NOWHERE' box. Also if related users have the message in that 
 * box then the message is deleted.
 */
class DeleteMessage extends \rocinante\controller\Command
{
   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager
    */
   private static $sqlm;

   /**
    * Creates a new message.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/DeleteMessage")
      {
         self::$sqlm = \rocinante\persistence\SqlManager::instance();

         // Get current user.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();

         // Get the message ID.
         $mailid = self::$sqlm->escape($this->request->getProperty('mailid'));

         // Move the message to 'NOWHERE' box.
         $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
         $mailboxAssembler = new \rocinante\persistence\DomainAssembler($mailboxFactory);
         $mailboxIdentity = $mailboxFactory->getIdentity();
         $mailboxIdentity->field("UserId")->eq($userid)->iand()->field("MailId")->eq($mailid);
         $collection = $mailboxAssembler->find($mailboxIdentity);
         $object = $collection->first();
         if ($object !== null)
         {
            $object->set('Box', 'NOWHERE');
            $mailboxAssembler->update($object);
         }

         // Check whether the message can be deleted.
         $mailboxCounter = new \rocinante\mapper\identity\Identity(array('MailId' => 'i', 'Box' => 's'), "MailBox");
         $mailboxCounter->count("Box")->field("MailId")->eq($mailid)->iand()->field("Box")->neq('NOWHERE');
         $result = $mailboxAssembler->find($mailboxCounter)->first();
         $totalRows = \intval($result->get('COUNT(Box)'));

         // If there's no rows, delete the message.
         if ($totalRows === 0)
         {
            $mailBoxIdentity = $mailboxFactory->getIdentity();
            $mailBoxIdentity->field("MailId")->eq($mailid);

            $mailFactory = new \rocinante\persistence\PersistenceFactory("Mail");
            $mailIdentity = $mailFactory->getIdentity();

            $messageFactory = new \rocinante\persistence\PersistenceFactory("Message");
            $messageIdentity = $messageFactory->getIdentity();

            $mailBoxIdentity->join($mailIdentity, "MailBox.MailId", "Mail.MailId");
            $mailBoxIdentity->join($messageIdentity, "MailBox.MailId", "Message.MailId");

            $mailboxAssembler->delete($mailBoxIdentity, array("Mail", "MailBox", "Message"));
         }

         // Make a response.
         $array["result"] = "ok";
         echo \json_encode($array);
      }
   }

}
