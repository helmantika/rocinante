<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/MailBoxIdentity.php';

/**
 * CountNewMessages creates a string that shows how many new messages a user has.
 */
class CountNewMessages extends \rocinante\controller\Command
{

   /**
    * Creates a string that shows how many new messages a user has.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/CountNewMessages")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         $factory = new \rocinante\persistence\PersistenceFactory("MailBox");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $mailboxIdentity = new \rocinante\mapper\identity\MailBoxIdentity();
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();
         $mailboxIdentity->distinct()->field('UserId')->eq($userid)->iand()->field('Box')->eq('IN')->iand()->field('IsRead')->eq(0);
         $collection = $assembler->find($mailboxIdentity);
         $counter = $collection->size();
         
         if ($counter === 1)
         {
            $string = $l10n->frontpage->{"new-message"};
         } 
         else if ($counter > 1)
         {
            $string = \sprintf($l10n->frontpage->{"new-messages"}, $counter);
         }
         else
         {
            $string = $l10n->frontpage->{"no-new-mail"};
         }

         echo $string;
      }
   }

}
