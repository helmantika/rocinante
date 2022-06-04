<?php

namespace rocinante\command\login;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/login/Proxy.php';
require_once 'rocinante/persistence/SqlManager.php';

/**
 * Login 
 */
class Login extends \rocinante\controller\Command
{
   /**
    * The proxy to Rocinante.
    * @var Proxy
    */
   private $proxy;

   /**
    * The username.
    * @var string 
    */
   private $username = null;

   /**
    * The password.
    * @var string 
    */
   private $password = null;
   
   /**
    * Sanitizes request data and delegates the proxy to login.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "login/Login")
      {
         $sqlm = \rocinante\persistence\SqlManager::instance();
         if ($this->request->getProperty('username') !== null)
         {
            $this->username = $sqlm->escape($this->request->getProperty('username'));
         }
         if ($this->request->getProperty('password') !== null)
         {
            $this->password = $sqlm->escape($this->request->getProperty('password'));
         }

         $this->login($this->proxy = new \rocinante\command\login\Proxy());
      }
   }

   /**
    * Delegates the proxy to check if data is right.
    */
   private function login(\rocinante\command\login\Subject $proxy)
   {
      $proxy->login($this->username, $this->password);
   }
}