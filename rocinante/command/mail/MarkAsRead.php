<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * MarkAsRead marks a given message as already read.
 */
class MarkAsRead extends \rocinante\controller\Command
{
   /**
    * Marks a given message as already read.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/MarkAsRead")
      {
         // Get current user.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();
         
         // Get mail ID.
         $mailid = \intval($this->request->getProperty('mailid'));
         
         // Update MailBox.
         $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
         $mailboxAssembler = new \rocinante\persistence\DomainAssembler($mailboxFactory);
         $mailboxIdentity = $mailboxFactory->getIdentity();
         $mailboxIdentity->field("MailId")->eq($mailid)->iand()->field("UserId")->eq($userid)->iand()->field("Box")->eq("IN");
         $collection = $mailboxAssembler->find($mailboxIdentity);
         $object = $collection->first();
         if ($object !== null)
         {
            $object->set('IsRead', 1);
            $mailboxAssembler->update($object);
            
            $array["result"] = "ok";
            echo \json_encode($array);
         }
      }
   }
}
