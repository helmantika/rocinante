<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * InsertMessage creates a new message in mail system.
 */
class InsertMessage extends \rocinante\controller\Command
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
      if ($this->request->getProperty('cmd') === "mail/InsertMessage")
      {
         self::$sqlm = \rocinante\persistence\SqlManager::instance(); 
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         // Get current user.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $sender = $session->getUserId();
            
         // Validate addressees.
         $unknownUsers = array();
         $tokens = \strtok($this->request->getProperty('addressees'), ", ");
         while ($tokens !== false)
         {
            $addressees[] = $tokens;
            $tokens = \strtok(", ");
         }
         $users = $this->readUsers();
         $addresseesid = array();
         foreach($addressees as &$value)
         {
            $value = \trim($value);
            if (!empty($value))
            {
               if (!\array_key_exists($value, $users))
               {
                  $unknownUsers[] = $value;
               } 
               else if ($sender !== $users[$value])
               {
                  $addresseesid[$value] = $users[$value];
               }
            }
         }
         if (\count($addresseesid) === 0)
         {
            $message = (string) $l10n->{"dialog"}->{"mail"}->{"invalid-addressees"};
         }
         else if (\count($unknownUsers) > 0)
         {
            $message = \sprintf((string) $l10n->{"dialog"}->{"mail"}->{"unknown-addressees"}, \implode(', ', $unknownUsers));
         }
         // If addressee list is valid, insert a new message.
         else
         {
            // Get message data.
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $subject = self::$sqlm->escape($this->request->getProperty('subject'));
            $body = $this->request->getProperty('body');

            // If it was a draft, remove it.
            $draftid = \intval(self::$sqlm->escape($this->request->getProperty('isdraft')));
            if ($draftid !== 0)
            {
               $this->deleteDraft($draftid);
            }

            // Insert a new message.
            $messageFactory = new \rocinante\persistence\PersistenceFactory("Message");
            $messageAssembler = new \rocinante\persistence\DomainAssembler($messageFactory);
            $newMessage = $messageFactory->getDomainFactory()->createObject(array());
            $newMessage->set('Subject', $subject);
            $newMessage->set('Body', $body);
            $newMessage->set('Time', \date('Y-m-d H:i:s'));
            $messageAssembler->insert($newMessage);
            $mailid = $newMessage->get('MailId');

            // Bind sender to addressee(s).
            if ($mailid >= 0)
            {
               // Get a new chatid for this message if it isn't a reply.
               $chatid = \intval(self::$sqlm->escape($this->request->getProperty('chatid')));
               if ($chatid === 0)
               {
                  $chatid = $this->generareChatId();
               }

               // Insert bindings in 'Mail' table.
               $mailFactory = new \rocinante\persistence\PersistenceFactory("Mail");
               $mailAssembler = new \rocinante\persistence\DomainAssembler($mailFactory);
               foreach ($addresseesid as $userid)
               {
                  $newMail = $mailFactory->getDomainFactory()->createObject(array());
                  $newMail->set('MailId', $mailid);
                  $newMail->set('SenderId', $sender);
                  $newMail->set('AddresseeId', $userid);
                  $newMail->set('ChatId', $chatid);
                  $mailAssembler->insert($newMail);
               }

               // Leave the new message in mailboxes.
               $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
               $mailboxAssembler = new \rocinante\persistence\DomainAssembler($mailboxFactory);

               // Leave the new message in sender mailbox.
               $inbox = $mailboxFactory->getDomainFactory()->createObject(array());
               $inbox->set('MailId', $mailid);
               $inbox->set('UserId', $sender);
               $inbox->set('Box', 'OUT');
               $inbox->set('IsRead', 1);
               $mailboxAssembler->insert($inbox);

               // Leave the new message in addressee mailboxes.
               foreach ($addresseesid as $userid)
               {
                  $outbox = $mailboxFactory->getDomainFactory()->createObject(array());
                  $outbox->set('MailId', $mailid);
                  $outbox->set('UserId', $userid);
                  $outbox->set('Box', 'IN');
                  $outbox->set('IsRead', 0);
                  $mailboxAssembler->insert($outbox);
               }
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = (string) $l10n->{"frontpage"}->{"tabs"}->{"mail"}->{"message-sent-ok"};
         }
         else
         {
            $array["result"] = "null";
            $array["html"] = $message;
         }
         echo \json_encode($array);
      }
   }
   
   /**
    * Retrieves the user list.
    * @return array An array where keys are user IDs and values are usernames.
    */
   private function readUsers()
   {
      $factory = new \rocinante\persistence\PersistenceFactory("User");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i',
                                                                    'Username' => 's',
                                                                    'IsActive' => 'i'), "User");
      $userIdentity->field("IsActive")->eq(1)->orderByAsc("Username");
      $collection = $assembler->find($userIdentity);
      $generator = $collection->getGenerator();
      foreach($generator as $object)
      {
         $users[$object->get('Username')] = $object->get('UserId');
      }
      return $users;
   }
   
   /**
    * Generates a new chat ID for a message.
    * @return int A new chat ID or 1 if message is the first one.
    */
   private function generareChatId()
   {
      $statement = "SELECT MAX(ChatId)+1 AS ChatId FROM Mail";
      
      self::$sqlm->query($statement);
      $row = self::$sqlm->fetchAssoc($statement);
      self::$sqlm->close($statement);
      $chatid = 0;
      if ($row !== null)
      {
         $chatid = $row['ChatId'] === null ? 1 : $row['ChatId'];
      }
      return $chatid;
   }
   
   /**
    * Deletes a draft.
    * @param int $draftid A draft ID.
    */
   private function deleteDraft($draftid)
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
   }
}
