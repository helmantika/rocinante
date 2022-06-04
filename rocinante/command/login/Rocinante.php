<?php

namespace rocinante\command\login;

require_once 'rocinante/command/login/Subject.php';
require_once 'rocinante/command/login/CheckMaintenance.php';

/**
 * Rocinante defines the real object that the proxy represents. It creates web app main page.
 */
class Rocinante implements \rocinante\command\login\Subject
{

   use \rocinante\command\login\CheckMaintenance
   {
      checkMaintenance as private;
   }
   
   /**
    * Starts Rocinante. If it's being maintenanced, only administrators can access.
    */
   public function request()
   {
      if ($this->checkMaintenance())
      {
         include "rocinante/view/maintenance.php";
      }
      else
      {
         include "rocinante/view/rocinante.php";
      }
   }

}
