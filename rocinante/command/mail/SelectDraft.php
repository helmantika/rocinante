<?php

namespace rocinante\command\mail;

require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * SelectDraft builds an HTML table that show all the information of a given draft.
 */
class SelectDraft
{

   const COLUMN_MAILID = 0;
   const COLUMN_ADDRESSEES = 1;
   const COLUMN_DATETIME = 2;
   const COLUMN_SUBJECT = 3;
   const COLUMN_BODY = 4;
   const COLUMN_BOX = 5;

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
      if ($this->request->getProperty('cmd') === "mail/ListDrafts")
      {
         // Build the query.
         $this->mailFactory = new \rocinante\persistence\PersistenceFactory("Mail");
         $this->mailAssembler = new \rocinante\persistence\DomainAssembler($this->mailFactory);

         $mailIdentity = $this->mailFactory->getIdentity();
         $mailIdentity->field("MailId")->eq($mailid)->iand()->field("SenderId")->eq($userid);

         $messageFactory = new \rocinante\persistence\PersistenceFactory("Message");
         $messageIdentity = $messageFactory->getIdentity();

         $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
         $mailboxIdentity = $mailboxFactory->getIdentity();
         $mailboxIdentity->field("Box")->eq("DRAFT");

         $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");

         $mailIdentity->join($messageIdentity, "Mail.MailId", "Message.MailId");
         $mailIdentity->join($mailboxIdentity, "Mail.MailId", "MailBox.MailId");
         $mailIdentity->join($userIdentity, "Mail.AddresseeId", "User.UserId");

         $collection = $this->mailAssembler->find($mailIdentity);
         $rows = array();
         $this->buildRows($collection, $rows);
         $html = "<div><p>" . \str_replace("\n", '<br />', $rows[0][self::COLUMN_BODY]) . "</p></div>";
         
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
                         $object->get('User.Username'),
                         $object->get('Message.Time'),
                         $object->get('Message.Subject'),
                         $object->get('Message.Body'),
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

}
