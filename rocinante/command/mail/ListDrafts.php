<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/mail/ListMessages.php';

/**
 * ListDrafts creates HTML code for a jQuery UI Accordion that shows all the messages the current 
 * user wrote and doesn't send.
 */
class ListDrafts extends \rocinante\controller\Command
{
   /**
    * Creates HTML code for a jQuery UI Accordion that shows all the messages the current  user 
    * wrote and doesn't send.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/ListDrafts")
      {
         $command = new \rocinante\command\mail\ListMessages("DRAFT", $this->request);
         $command->execute();
      }
   }
}
