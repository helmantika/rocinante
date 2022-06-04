<?php

namespace rocinante\command\mail;

require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/command/mail/SelectMessage.php';
require_once 'rocinante/command/mail/SelectDraft.php';

/**
 * ListMessages creates HTML code for a jQuery UI Accordion that shows all the messages stored in 
 * inbox, outbox, or draft box.
 */
class ListMessages
{

   const COLUMN_SUBJECT = 0;
   const COLUMN_SENDER = 1;
   const COLUMN_ADDRESSEES = 2;
   const COLUMN_DATETIME = 3;
   const COLUMN_MAILID = 4;
   const COLUMN_CHATID = 5;
   const COLUMN_ISREAD = 6;
   const COLUMN_BOX = 7;

   /**
    * Sets the mandatory arguments for executing this command.
    * @param string $box IN, OUT, or DRAFT.
    * @param \rocinante\controller\Request $request A request.
    */
   public function __construct($box, $request)
   {
      $this->box = $box;
      $this->request = $request;
      // Data to build the proper query.
      $this->mailIdentityUser = $box === "IN" ? "Mail.AddresseeId" : "Mail.SenderId";
      $this->userIdentityUser = $box === "IN" ? "Mail.SenderId" : "Mail.AddresseeId";
   }

   /**
    * Creates HTML code for a jQuery UI Accordion that shows all the messages the current user 
    * wrote and doesn't send.
    */
   public function execute()
   {
      if ($this->box !== "IN" && $this->box !== "OUT" && $this->box !== "DRAFT")
      {
         throw new Exception("Invalid box argument");
      }

      if ($this->request->getProperty('cmd') === "mail/ListInbox" ||
          $this->request->getProperty('cmd') === "mail/ListOutbox" ||
          $this->request->getProperty('cmd') === "mail/ListDrafts")
      {
         $html = "";
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $tableL10n = $l10n->{"frontpage"}->{"tabs"}->{"mail"}->{"table"};
         $action = array("IN" => $tableL10n->{"received"},
                         "OUT" => $tableL10n->{"sent"},
                         "DRAFT" => $tableL10n->{"created"});
         $message = new \rocinante\command\mail\SelectMessage($this->request);
         $draft = new \rocinante\command\mail\SelectDraft($this->request);

         // Get current user.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();
         $username = $session->getUser();
         
         // Build the query.
         $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
         $mailboxAssembler = new \rocinante\persistence\DomainAssembler($mailboxFactory);
         $mailboxIdentity = $mailboxFactory->getIdentity();
         $mailboxIdentity->field("UserId")->eq($userid)->iand()->field("Box")->eq($this->box);
         $mailFactory = new \rocinante\persistence\PersistenceFactory("Mail");
         $mailIdentity = $mailFactory->getIdentity();
         $messageFactory = new \rocinante\persistence\PersistenceFactory("Message");
         $messageIdentity = $messageFactory->getIdentity();
         $messageIdentity->orderByDesc("Time");
         $userFactory = new \rocinante\persistence\PersistenceFactory("User");
         $userIdentity = $userFactory->getIdentity();
         $mailboxIdentity->join($mailIdentity, array("MailBox.MailId", "MailBox.UserId"), array("Mail.MailId", $this->mailIdentityUser));
         $mailboxIdentity->join($messageIdentity, "Mail.MailId", "Message.MailId");
         $mailboxIdentity->join($userIdentity, $this->userIdentityUser, "User.UserId");
         $collection = $mailboxAssembler->find($mailboxIdentity);
         $rows = array();
         $this->buildRows($collection, $rows, $username);
         
         $totalRows = \count($rows);
         $rpp = \intval($this->request->getProperty('rpp'));
         $totalPages = (int) \ceil($totalRows / $rpp);
         $page = \intval($this->request->getProperty('page'));
         
         $start = ($page - 1) * $rpp;
         $max = $totalRows - $start > $rpp ? $rpp + $start : $totalRows;
         for ($i = $start; $i < $max; $i++)
         {
            $row = $rows[$i];
            $html .= "<div>\n<table>\n<tbody>\n";
            $html .= "<tr>";
            $html .= "<td class='message-subject'>";
            $html .= $row[self::COLUMN_ISREAD] !== 1 ? "<strong>" . $row[self::COLUMN_SUBJECT] . "</strong>" : $row[self::COLUMN_SUBJECT];
            $html .= "</td>";
            $html .= "</tr>\n";
            $html .= "<tr>";
            $html .= "<td class='message-info'>";
            $datetime = \explode(" ", $row[self::COLUMN_DATETIME]);
            $date = \DateTime::createFromFormat('Y-n-j', $datetime[0]);
            $time = \DateTime::createFromFormat('H:i:s', $datetime[1]);
            $html .= $tableL10n->{"from"};
            $html .= " <span id='sender'>";
            $html .= $row[self::COLUMN_SENDER] !== $username ? "<strong>{$row[self::COLUMN_SENDER]}</strong>" : $username;
            $html .= "</span> ";
            $html .= $tableL10n->{"to"};
            $html .= " <span id='addressees'>";
            $addressees = \explode(', ', $row[self::COLUMN_ADDRESSEES]);
            foreach ($addressees as $index => $name)
            {
               $addressees[$index] = ($name !== $username ? "<strong>$name</strong>" : $name);
            }
            $html .= \implode(', ', $addressees);
            $html .= "</span>. ";
            $html .= \sprintf($action[$this->box], $date->format($l10n->{"format"}->{"date-format"}), $time->format($l10n->{"format"}->{"time-format"}));
            $html .= "</td>";
            $html .= "</tr>\n";
            $html .= "<tr style='display: none;'><td>{$row[self::COLUMN_MAILID]}</td></tr>\n";
            $html .= "<tr style='display: none;'><td>{$row[self::COLUMN_CHATID]}</td></tr>\n";
            $html .= "<tr style='display: none;'><td>{$row[self::COLUMN_BOX]}</td></tr>\n";
            $html .= "<tr style='display: none;'><td>{$row[self::COLUMN_ISREAD]}</td></tr>\n";
            $html .= "</tbody>\n</table>\n</div>\n";
            if ($this->box === "DRAFT")
            {
               $html .= $draft->execute($userid, $row[self::COLUMN_MAILID]);
            }
            else
            {
               $html .= $message->execute($userid, $row[self::COLUMN_MAILID]);
            }
         }        
         
         if ($totalRows === 0)
         {
            $html  = "<div>\n<table>\n<tbody>\n";
            $html .= "<tr><td class='ui-widget-content' style='width: 100%; text-align: center'>" . $l10n->{"frontpage"}->{"tabs"}->{"mail"}->{"no-mail"} . "</td></tr>\n";
            $html .= "</tbody>\n</table>\n</div>\n";
         }
         
         $response = array("count" => $totalRows, "page" => $page, "total" => $totalPages, "html" => $html);
         echo \json_encode($response);
      }
   }

   /**
    * Filters object so that usernames of the same MailId are grouped in one row.
    * @param \rocinante\domain\collection\Collection $collection A domain object collection.
    * @param array $rows Created rows are added to this argument.
    */
   private function buildRows(&$collection, &$rows, $username)
   {
      $lastRow = null;
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         if ($lastRow === null || $object->get('Mail.MailId') !== $lastRow[self::COLUMN_MAILID])
         {
            $row = array($object->get('Message.Subject'),
                         $object->get('MailBox.Box') === 'IN' ? $object->get('User.Username') : $username,
                         $object->get('MailBox.Box') === 'IN' ? $this->selectAdreessees($object->get('Mail.MailId')) : $object->get('User.Username'),
                         $object->get('Message.Time'),
                         $object->get('Mail.MailId'),
                         $object->get('Mail.ChatId'),
                         $object->get('MailBox.IsRead'),
                         $object->get('MailBox.Box'));
            $rows[] = $row;
            $lastRow = $row;
         }
         else
         {
            $lastRow[self::COLUMN_ADDRESSEES] .= ($lastRow[self::COLUMN_ADDRESSEES] !== null ? ", " : "") . $object->get('User.Username');
            $rows[\count($rows) - 1] = $lastRow;
         }
      }
   }
   
   /**
    * Select addressee names for a given mail ID.
    * @param int $mailid A mail ID.
    * @return string A comma separated string with addressee names.
    */
   private function selectAdreessees($mailid)
   {
      $addreessees = array();
      $mailFactory = new \rocinante\persistence\PersistenceFactory("Mail");
      $mailAssembler = new \rocinante\persistence\DomainAssembler($mailFactory);
      $mailIdentity = $mailFactory->getIdentity();
      $mailIdentity->field('MailId')->eq($mailid);
      $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
      $mailIdentity->join($userIdentity, "Mail.AddresseeId", "User.UserId");
      $collection = $mailAssembler->find($mailIdentity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $addreessees[] = $object->get('User.Username');
      }
      return \implode(', ', $addreessees);
   }

}
