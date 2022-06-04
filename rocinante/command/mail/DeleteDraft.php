<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * DeleteDraft removes a draft and all its related information.
 */
class DeleteDraft extends \rocinante\controller\Command
{

   /**
    * Deletes a draft and all its related information.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/DeleteDraft")
      {
         $draftid = \intval($this->request->getProperty('draftid'));
         if ($draftid !== 0)
         {
            $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
            $mailboxAssembler = new \rocinante\persistence\DomainAssembler($mailboxFactory);
            $mailBoxIdentity = $mailboxFactory->getIdentity();
            $mailBoxIdentity->field("MailId")->eq($draftid)->iand()->field("Box")->eq("DRAFT");

            $mailFactory = new \rocinante\persistence\PersistenceFactory("Mail");
            $mailIdentity = $mailFactory->getIdentity();

            $messageFactory = new \rocinante\persistence\PersistenceFactory("Message");
            $messageIdentity = $messageFactory->getIdentity();

            $mailBoxIdentity->join($mailIdentity, "MailBox.MailId", "Mail.MailId");
            $mailBoxIdentity->join($messageIdentity, "MailBox.MailId", "Message.MailId");

            $mailboxAssembler->delete($mailBoxIdentity, array("Mail", "MailBox", "Message"));
            
            // Make a response.
            $array["result"] = "ok";
            echo \json_encode($array);
         }
      }
   }

}
