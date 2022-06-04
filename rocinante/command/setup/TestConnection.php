<?php

namespace rocinante\command\setup;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR);

/**
 * TestConnection checks if Rocinante can connect to a MySQL database.
 */
class TestConnection extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request. 
    * @var array  
    */
   private $validation = array('host'     => array('IsMinLength(1)'),
                               'database' => array('IsMinLength(1)'),
                               'username' => array('IsMinLength(1)'),
                               'password' => array('IsMinLength(1)'));

   /**
    * Checks if Rocinante can connect to a given database.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "setup/TestConnection")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);

         // If everything is right, try to connect to database.
         if (empty($message))
         {                           
            $mysqli = new \mysqli($this->request->getProperty('host')['value'],
                                  $this->request->getProperty('username')['value'],
                                  $this->request->getProperty('password')['value'],
                                  $this->request->getProperty('database')['value']);
            
            if (!$mysqli->connect_error)
            {
               $array["result"] = "ok";
               $array["html"] = (string) $l10n->{"setup"}->{"right-connection"};
            } 
            else
            {
               $array["result"] = "null";
               $array["html"] = (string) $l10n->{"setup"}->{"failed-connection"} . $mysqli->connect_error;
            }
         }
         else
         {
            $array["result"] = "null";
            $array["html"] = (string) $l10n->{"setup"}->{"failed-connection"} . "Missing information";
         }

         echo \json_encode($array);
      }
   }
}

