<?php

namespace rocinante\command\setup;

require_once 'rocinante/persistence/SqlImport.php';
require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';

/**
 * CreateDatabase creates Rocinante's database structure.
 */
class CreateDatabase extends \rocinante\controller\Command
{
   use \rocinante\persistence\SqlImport
   {
      sqlImport as private;
      removeComments as private;
      isQuoted as private;
   }
   
   /**
    * Specifies how to validate fields coming from a request. 
    * @var array  
    */
   private $validation = array('host' => array('IsMinLength(1)'),
                               'database' => array('IsMinLength(1)'),
                               'username' => array('IsMinLength(1)'),
                               'password' => array('IsMinLength(1)'));
   /**
    * The object which represents a connection to a MySQL server.
    * @var \mysqli
    */
   private $mysqli;

   /**
    * Creates tables, procedures, triggers, and indices to store ESO strings and their translation.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "setup/CreateDatabase")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $fail = (string) $l10n->{"setup"}->{"failed-connection"};
         
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);

         // If everything is right, create database structure.
         if (empty($message))
         {          
            $host = $this->request->getProperty('host')['value'];
            $username = $this->request->getProperty('username')['value'];
            $password = $this->request->getProperty('password')['value'];
            $database = $this->request->getProperty('database')['value'];
            $this->mysqli = new \mysqli($host, $username, $password, $database);
            
            if (!$this->mysqli->connect_error)
            {
               $this->mysqli->set_charset("utf8");

               $sql = \file("rocinante/command/setup/rocinante.sql");
               if ($this->sqlImport($this->mysqli, $sql))
               {
                  $this->createConnectFile($host, $username, $password, $database);
                  $array["result"] = "ok";
                  $array["html"] = (string) $l10n->{"setup"}->{"right-connection"};
               }
               else
               {
                  $array["result"] = "null";
                  $array["html"] = $fail . $this->mysqli->error;
               }
            }
            else
            {
               $array["result"] = "null";
               $array["html"] = $fail . $this->mysqli->connect_error;
            }
         }
         else
         {
            $array["result"] = "null";
            $array["html"] = $fail . "Missing information";
         }

         echo \json_encode($array);
      }
   }

   /**
    * Create connect.xml. This file is used by Rocinante to connect to the database.
    */
   private function createConnectFile($host, $username, $password, $database)
   {
      $content  = "<?xml version='1.0' encoding='UTF-8'?>\n";
      $content .= "<connect>\n";
      $content .= "   <host>$host</host>\n";
      $content .= "   <user>$username</user>\n";
      $content .= "   <password>$password</password>\n";
      $content .= "   <database>$database</database>\n";
      $content .= "</connect>\n";

      $file = \fopen("config/connect.xml", "w");
      \fwrite($file, $content);
   }
}
