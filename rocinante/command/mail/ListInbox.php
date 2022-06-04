<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/mail/ListMessages.php';

/**
 * ListInbox creates HTML code for a jQuery UI Accordion that shows all the messages the current 
 * user received.
 */
class ListInbox extends \rocinante\controller\Command
{
   /**
    * Creates HTML code for a jQuery UI Accordion that shows all the messages the current user 
    * received.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/ListInbox")
      {
         $command = new \rocinante\command\mail\ListMessages("IN", $this->request);
         $command->execute();
      }
   }
}
