<?php

namespace rocinante\command\login;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/login/Proxy.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * Autologin delegates the proxy to start Rocinante.
 */
class Autologin extends \rocinante\controller\Command
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
   private $username;

   /**
    * Delegates the Proxy.
    */
   public function doExecute()
   {
      $this->username = \rocinante\command\SessionRegistry::instance()->getUser();
      $this->autologin($this->proxy = new \rocinante\command\login\Proxy());
   }

   /**
    * Delegates the proxy to start Rocinante.
    */
   private function autologin(\rocinante\command\login\Subject $proxy)
   {
      $proxy->autologin($this->username);
   }

}
