<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/mail/ListMessages.php';

/**
 * ListOutbox creates HTML code for a jQuery UI Accordion that shows all the messages the current 
 * user sent.
 */
class ListOutbox extends \rocinante\controller\Command
{

   /**
    * Creates HTML code for a jQuery UI Accordion that shows all the messages the current user sent.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/ListOutbox")
      {
         $command = new \rocinante\command\mail\ListMessages("OUT", $this->request);
         $command->execute();
      }
   }

}
