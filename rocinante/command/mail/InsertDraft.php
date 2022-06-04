<?php

namespace rocinante\command\mail;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * InsertDraft creates a new draft in mail system.
 */
class InsertDraft extends \rocinante\controller\Command
{
   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager 
    */
   private static $sqlm;
   
   /**
    * Creates a new draft.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "mail/InsertDraft")
      {
         self::$sqlm = \rocinante\persistence\SqlManager::instance(); 
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();

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
               else
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
         // If addressee list is valid, insert a new draft.
         else
         {
            // Get current user.
            $session = \rocinante\command\SessionRegistry::instance();
            $session->resume();
            $sender = $session->getUserId();

            // Get message data.
            $sqlm = \rocinante\persistence\SqlManager::instance();
            $subject = self::$sqlm->escape($this->request->getProperty('subject'));
            $body = $this->request->getProperty('body');

            // Insert a new draft.
            $draftid = \intval(self::$sqlm->escape($this->request->getProperty('draftid')));
            $messageFactory = new \rocinante\persistence\PersistenceFactory("Message");
            $messageAssembler = new \rocinante\persistence\DomainAssembler($messageFactory);
            $newMessage = $messageFactory->getDomainFactory()->createObject(array());
            if ($draftid > 0 )
            {
               $newMessage->set('MailId', $draftid);
            }
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

               // Leave the new message in sender mailbox.
               $mailboxFactory = new \rocinante\persistence\PersistenceFactory("MailBox");
               $mailboxAssembler = new \rocinante\persistence\DomainAssembler($mailboxFactory);
               $box = $mailboxFactory->getDomainFactory()->createObject(array());
               $box->set('MailId', $mailid);
               $box->set('UserId', $sender);
               $box->set('Box', 'DRAFT');
               $box->set('IsRead', 1);
               $mailboxAssembler->insert($box);
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = (string) $l10n->{"frontpage"}->{"tabs"}->{"mail"}->{"draft-saved-ok"};
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

}
