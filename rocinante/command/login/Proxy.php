<?php

namespace rocinante\command\login;

require_once 'rocinante/command/login/Subject.php';
require_once 'rocinante/command/login/Rocinante.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/persistence/PersistenceFactory.php';
require_once 'rocinante/persistence/DomainAssembler.php';

/**
 * Proxy maintains a reference that lets the proxy access Rocinante, provides an interface identical
 * to Subject's so that a proxy can be substituted for the real subject, and controls access to
 * Rocinante and may be responsible for creating and deleting it.
 */
class Proxy implements \rocinante\command\login\Subject
{

   /**
    * The object which manages user information.
    * @var \rocinante\persistence\DomainAssembler
    */
   private $domainAssembler;

   /**
    * The object that gets factories that are bound to domain objects.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $persistenceFactory;

   /**
    * The real subject.
    * @var Rocinante
    */
   private $rocinante;

   /**
    * Initializes properties.
    */
   public function __construct()
   {
      $this->persistenceFactory = new \rocinante\persistence\PersistenceFactory("User");
      $this->domainAssembler = new \rocinante\persistence\DomainAssembler($this->persistenceFactory);
   }

   /**
    * Receives the username and password, verifies the password, opens the database, and queries the
    * table.
    * @param string $username
    * @param string $password
    * @return bool 
    */
   public function login($username, $password)
   {
      $result = false;

      // Check if information is right.
      $user = $this->findUser($username);
      if ($user !== null)
      {
         $storedPassword = $user->get('Password');
         $result = $storedPassword !== null && \password_verify($password, $storedPassword);

         // If everthing is right, send the request to Rocinante.
         if ($result)
         {
            $this->createSession($username);
            $this->request();
         }
      }

      // Is something went wrong, send a response to the client.
      if (!$result)
      {
         echo 'null';
      }
   }

   /**
    * Starts Rocinante automatically for the given user.
    * @param string $username
    */
   public function autologin($username)
   {
      $this->createSession($username);
      $this->request();
   }

   /**
    * Calls Rocinante request method to start the app.
    */
   public function request()
   {
      $this->rocinante = new \rocinante\command\login\Rocinante();
      $this->rocinante->request();
   }

   /**
    * Creates or renews the session ID for the given user.
    * @param string $username
    */
   private function createSession($username)
   {
      $registry = \rocinante\command\SessionRegistry::instance();
      $registry->start();

      // Store username for this session.
      $registry->setUser($username);

      // Retrieve user information to store ID, and UI theme.
      $user = $this->findUser($username);
      if ($user === null)
      {
         throw new Exception("User $username not found in database");
      }

      $registry->setUserId($user->get('UserId'));
      $registry->setTheme($user->get('Theme'));
      $registry->setType($user->get('Type'));

      // Insert session ID in the database.
      $sessionId = session_id();
      $user->set('SessionId', $sessionId);
      $this->domainAssembler->update($user);

      // Leave a cookie for a week.
      setcookie("RocinanteSID", $sessionId, time() + 7 * 24 * 3600);
   }

   /**
    * Find a user in the database.
    * @param string $username The user name to look for.
    * @return \rocinante\domain\User A user object or null.
    */
   private function findUser($username)
   {
      $userIdentity = $this->persistenceFactory->getIdentity();
      $userIdentity->field("Username")->eq($username);
      $collection = $this->domainAssembler->find($userIdentity);
      return $collection->first();
   }

}
