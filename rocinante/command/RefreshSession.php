<?php

namespace rocinante\command;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/login/CheckMaintenance.php';

/**
 * RefreshSession resets session timeout.
 */
class RefreshSession extends \rocinante\controller\Command
{

   use \rocinante\command\login\CheckMaintenance
   {
      checkMaintenance as private;
   }
   
   /**
    * Resets session timeout.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "RefreshSession")
      {
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $array["result"] = $this->checkMaintenance() ? "fail" : "ok";
         echo \json_encode($array);
      }
   }

}
