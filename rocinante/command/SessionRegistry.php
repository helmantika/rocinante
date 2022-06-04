<?php

namespace rocinante\command;

/**
 * SessionRegistry defines an interface to provide systemwide access to session variables.
 */
class SessionRegistry
{

   /**
    * The one and only instance of this class.
    * @var RequestRegistry
    */
   private static $instance = null;

   /**
    * RequestRegistry can't be instanced directly.
    */
   private function __construct()
   {

   }

   /**
    * Returnst the one and only instance of this class.
    * @return RequestRegistry The RequestRegistry instance.
    */
   public static function instance()
   {
      if (is_null(self::$instance))
      {
         self::$instance = new self();
      }
      return self::$instance;
   }

   /**
    * Starts a session and regenerates the session ID.
    */
   public function start()
   {
      \session_start();
      \session_regenerate_id();
   }
   
   /**
    * Resumes the existing session. If session variables are gone then informs and aborts.
    */
   public function resume()
   {
      if (\session_status() === \PHP_SESSION_NONE)
      {
         \session_start();
         if ($this->getUserId() === null)
         {
            echo \json_encode(array( "result" => "SESSION_IS_NOT_SET"));
            exit;
         }
      }
   }
   
   /**
    * Destroys the existing session.
    */
   public function destroy()
   {
      \session_unset();
      \session_destroy(); 
   }
   
   /**
    * Returns the value of the specified session variable.
    * @param string $key A key given as an string.
    */
   protected function get($key)
   {
      $value = null;
      if (isset($_SESSION[__CLASS__][$key]))
      {
         $value = $_SESSION[__CLASS__][$key];
      }
      return $value;
   }

   /**
    * Sets a value for the specified session variable.
    * @param string $key A key given as an string.
    * @param mixed $value A value of any type.
    */
   protected function set($key, $value)
   {
      $_SESSION[__CLASS__][$key] = $value;
   }

   /**
    * Sets the user ID what the session is created or renewed for.
    * @param string $userid A user ID.
    */
   public function setUserId($userid)
   {
      self::$instance->set('userid', $userid);
   }

   /**
    * Gets the user ID what the session is created or renewed for.
    * @return string $userid A user ID.
    */
   public function getUserId()
   {
      return self::$instance->get('userid');
   }

   /**
    * Sets the user who creates or renews the session.
    * @param string $username The user name.
    */
   public function setUser($username)
   {
      self::$instance->set('username', $username);
   }

   /**
    * Gets the user who created or renewed the session.
    * @return string The user name.
    */
   public function getUser()
   {
      return self::$instance->get('username');
   }

   /**
    * Sets the type of user.
    * @param string $category ADMIN, ADVISOR, or TRANSLATOR.
    */
   public function setType($category)
   {
      self::$instance->set('type', $category);
   }

   /**
    * Gets the type of user.
    * @param string $category ADMIN, ADVISOR, or TRANSLATOR.
    */
   public function getType()
   {
      return self::$instance->get('type');
   }

   /**
    * Sets the UI theme chose by the user.
    * @param string $theme A theme name.
    */
   public function setTheme($theme)
   {
      self::$instance->set('theme', $theme);
   }

   /**
    * Gets the UI theme chose by the user.
    * @return string A theme name.
    */
   public function getTheme()
   {
      return self::$instance->get('theme');
   }

}
