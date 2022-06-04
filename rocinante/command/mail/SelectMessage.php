<?php

namespace rocinante\command\mail;

require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * SelectMessage builds an HTML table that show all the information of a given message and its 
 * related messages.
 */
class SelectMessage
{

   const COLUMN_MAILID = 0;
   const COLUMN_SENDER = 1;
   const COLUMN_ADDRESSEES = 2;
   const COLUMN_DATETIME = 3;
   const COLUMN_SUBJECT = 4;
   const COLUMN_BODY = 5;
   const COLUMN_BOX = 6;

   /**
    * The Mail persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $mailFactory;

   /**
    * The Mail object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $mailAssembler;

   /**
    * Sets the mandatory arguments for executing this command.
    * @param \rocinante\controller\Request $request A request.
    */
   public function __construct($request)
   {
      $this->request = $request;
   }
   
   /**
    * Builds an HTML table that show all the information of a given message and its related messages.
    */
   public function execute($userid, $mailid)
   {
      if ($this->request->getProperty('cmd') === "mail/ListInbox" ||
          $this->request->getProperty('cmd') === "mail/ListOutbox")
      {
         $html = "";
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $tableL10n = $l10n->{"frontpage"}->{"tabs"}->{"mail"}->{"table"};

         // Build the query.
         $this->mailFactory = new \rocinante\persistence\PersistenceFactory("Mail");
         $this->mailAssembler = new \rocinante\persistence\DomainAssembler($this->mailFactory);

         // Get the chat ID and time of the message.
         $condition = $this->readConditions($mailid);

         $mailIdentity = $this->mailFactory->getIdentity();
         $mailIdentity->field("ChatId")->eq($condition['chatid']);

         $messageFactory = new \rocinante\persistence\PersistenceFactory("Message");
         $messageIdentity = $messageFactory->getIdentity();
         $messageIdentity->field("Time")->le($condition['time'])->orderByDesc("Time");

         $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
         $mailboxIdentity = $mailboxFactory->getIdentity();
         $mailboxIdentity->field("UserId")->eq($userid)->iand()->lparen()->field("Box")->eq("IN")->ior()->field("Box")->eq("OUT")->rparen();

         $senderIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
         $senderIdentity->alias("Sender");
         $addresseeIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
         $addresseeIdentity->alias("Addressee");

         $mailIdentity->join($messageIdentity, "Mail.MailId", "Message.MailId");
         $mailIdentity->join($mailboxIdentity, "Mail.MailId", "MailBox.MailId");
         $mailIdentity->join($senderIdentity, "Mail.SenderId", "Sender.UserId");
         $mailIdentity->join($addresseeIdentity, "Mail.AddresseeId", "Addressee.UserId");

         $collection = $this->mailAssembler->find($mailIdentity);
         $rows = array();
         $this->buildRows($collection, $rows);
         $html .= "<div>";
         $html .= "<p>" . \str_replace("\n", '<br />', $rows[0][self::COLUMN_BODY]) . "</p>";
         
         for ($i = 1; $i < \count($rows); $i++)
         {
            $row = $rows[$i];
            if ($i === 1)
            {
               $html .= "<div class='ui-widget-header ui-corner-all related-messages-caption'>" . $l10n->{"frontpage"}->{"tabs"}->{"mail"}->{"related-messages"} . "</div>";
               $html .= "<div class='related-messages-height'>";
            }
            $html .= "<div class='ui-widget-content ui-corner-all related-messages-header'>";
            $html .= "<p class='related-messages-subject'><strong>{$row[self::COLUMN_SUBJECT]}</strong></p>";
            $html .= "<p class='related-messages-info'>";
            $datetime = \explode(" ", $row[self::COLUMN_DATETIME]);
            $date = \DateTime::createFromFormat('Y-n-j', $datetime[0]);
            $time = \DateTime::createFromFormat('H:i:s', $datetime[1]);
            $html .= $tableL10n->{"from"} . " <strong>" . $row[self::COLUMN_SENDER] . "</strong> ";
            $html .= $tableL10n->{"to"} . " <strong>" . $row[self::COLUMN_ADDRESSEES] . "</strong>. ";
            if ($row[self::COLUMN_BOX] === "IN")
            {
               $html .= \sprintf($tableL10n->{"received"}, $date->format($l10n->{"format"}->{"date-format"}), $time->format($l10n->{"format"}->{"time-format"}));               
            }
            else // OUT or DRAFT
            {
               $html .= \sprintf($row[self::COLUMN_BOX] === "OUT" ? $tableL10n->{"sent"} : $tableL10n->{"created"}, $date->format($l10n->{"format"}->{"date-format"}), $time->format($l10n->{"format"}->{"time-format"}));
            }
            $html .= "</p></div>";
            $html .= "<p>" . \str_replace("\n", '<br />', $row[self::COLUMN_BODY]) . "</p>";
         }
         if (\count($rows) > 1)
         {
            $html .= "</div>";
         }
         $html .= "</div>";
         
         return $html;
      }
   }

   /**
    * Filters object so that usernames of the same MailId are grouped in one row.
    * @param \rocinante\domain\collection\Collection $collection A domain object collection.
    * @param array $rows Adds created rows to this argument.
    */
   private function buildRows(&$collection, &$rows)
   {
      $lastRow = null;
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         if ($lastRow === null || $object->get('Mail.MailId') !== $lastRow[self::COLUMN_MAILID])
         {
            $row = array($object->get('Mail.MailId'),
                         $object->get('Sender.Username'),
                         $object->get('Addressee.Username'),
                         $object->get('Message.Time'),
                         $object->get('Message.Subject'),
                         $object->get('Message.Body'),
                         $object->get('MailBox.Box'));
            $rows[] = $row;
            $lastRow = $row;
         } else
         {
            $lastRow[self::COLUMN_ADDRESSEES] .= ($lastRow[self::COLUMN_ADDRESSEES] !== null ? ", " : "") . $object->get('Addressee.Username');
            $rows[\count($rows) - 1] = $lastRow;
         }
      }
   }

   /**
    * Reads the chat ID and time of the message
    * @param int $mailid A message ID.
    * @return array An array where keys are 'chatid' and 'time'.
    * @throws Exception Invalid data.
    */
   private function readConditions($mailid)
   {
      $mailIdentity = new \rocinante\mapper\identity\Identity(array('MailId' => 'i', 'ChatId' => 'i'), "Mail");
      $mailIdentity->field("MailId")->eq($mailid);
      $messageIdentity = new \rocinante\mapper\identity\Identity(array('MailId' => 'i', 'Time' => 's'), "Message");
      $mailIdentity->join($messageIdentity, 'MailId', 'MailId');
      $collection = $this->mailAssembler->find($mailIdentity);
      $result = $collection->first();
      if ($result === null)
      {
         throw new Exception("Invalid data");
      }
      return array("chatid" => $result->get('Mail.ChatId'), "time" => $result->get('Message.Time'));
   }

}
