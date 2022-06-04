<?php

namespace rocinante\command\update;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/command/addon/FileUtils.php';

/**
 * DumpFiles calls a CGI script that compares current lang/lua file information to the new file set.
 * If everything goes right, the script will create six SQL files to update the database.
 */
class DumpFiles extends \rocinante\controller\Command
{
   const BASE_CLIENT_LUA = 0;
   const BASE_PREGAME_LUA = 1;
   const BASE_LANG = 2;
   const EXTRA_CLIENT_LUA = 3;
   const EXTRA_PREGAME_LUA = 4;
   const EXTRA_LANG = 5;
   const CGI_SUCCESS = 10;

   use \rocinante\command\addon\FileUtils
   {
      unzip as private;
   }

   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager 
    */
   private static $sqlm;
   
   /**
    * The main language.
    * @var string
    */
   private $baselang;

   /**
    * The second language.
    * @var string
    */
   private $extralang;
   
   /**
    * The update consists of a lot of steps. The hard work is done by a CGI script.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "update/DumpFiles")
      {
         $message = null;
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         
         if (isset($_FILES) && \count($_FILES) === 6)
         {
            $viewhelper = \rocinante\view\ViewHelper::instance();
            $this->baselang = (string) $viewhelper->getConfig()->{"baselang"};
            $this->extralang = (string) $viewhelper->getConfig()->{"extralang"};

            // Handle files.
            $this->moveOldFiles();
            $this->uploadNewFiles();

            // Call the CGI.
            $result = $this->callCgi($this->baselang, $this->extralang);
            if (\intval($result) === self::CGI_SUCCESS)
            {
               self::$sqlm = \rocinante\persistence\SqlManager::instance();
               
               $this->prepareDatabase();
               $this->addNewLang();
               $this->addNewLua();
            }
            else
            {
               // On error, undo actions related to files.
               $this->restoreOldFiles();
               
               // Report the error.
               $error = "error$result";
               $message = (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->$error;
            }
         }
         else
         {
            // Report the error.
            $error = "error0";
            $message = (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->$error;
         }
      }
      
      $response = array("result" => !empty($message) ? "fail" : "ok", "html" => $message);
      echo \json_encode($response);
   }
   
   /**
    * Moves current lua and lang files to update folder and renames them by adding "old" suffix.
    */
   private function moveOldFiles()
   {
      \rename("./esodata/{$this->baselang}_client.lua", "./update/{$this->baselang}_client_old.lua");
      \rename("./esodata/{$this->baselang}_pregame.lua", "./update/{$this->baselang}_pregame_old.lua");
      \rename("./esodata/{$this->baselang}.lang", "./update/{$this->baselang}_old.lang");
      \rename("./esodata/{$this->extralang}_client.lua", "./update/{$this->extralang}_client_old.lua");
      \rename("./esodata/{$this->extralang}_pregame.lua", "./update/{$this->extralang}_pregame_old.lua");
      \rename("./esodata/{$this->extralang}.lang", "./update/{$this->extralang}_old.lang");
   }
   
   /**
    * Undo file movement that is done by moveOldFiles().
    */
   private function restoreOldFiles()
   {
      \rename("./update/{$this->baselang}_client_old.lua", "./esodata/{$this->baselang}_client.lua");
      \rename("./update/{$this->baselang}_pregame_old.lua", "./esodata/{$this->baselang}_pregame.lua");
      \rename("./update/{$this->baselang}_old.lang", "./esodata/{$this->baselang}.lang");
      \rename("./update/{$this->extralang}_client_old.lua", "./esodata/{$this->extralang}_client.lua");
      \rename("./update/{$this->extralang}_pregame_old.lua", "./esodata/{$this->extralang}_pregame.lua");
      \rename("./update/{$this->extralang}_old.lang", "./esodata/{$this->extralang}.lang");
   }

   /**
    * Upload new lua and lang files into "update" folder. 
    * @return true on success, false otherwise.
    */
   private function uploadNewFiles()
   {
      $result = 0;

      // Move lua files.
      if (\array_key_exists(self::BASE_CLIENT_LUA, $_FILES))
      {
         $result += (integer) \move_uploaded_file($_FILES[self::BASE_CLIENT_LUA]['tmp_name'],
                                                  "./esodata/{$this->baselang}_client.lua");
      }
      if (\array_key_exists(self::BASE_PREGAME_LUA, $_FILES))
      {
         $result += (integer) \move_uploaded_file($_FILES[self::BASE_PREGAME_LUA]['tmp_name'],
                                                  "./esodata/{$this->baselang}_pregame.lua");
      }
      if (\array_key_exists(self::EXTRA_CLIENT_LUA, $_FILES))
      {
         $result += (integer) \move_uploaded_file($_FILES[self::EXTRA_CLIENT_LUA]['tmp_name'],
                                                  "./esodata/{$this->extralang}_client.lua");
      }
      if (\array_key_exists(self::EXTRA_PREGAME_LUA, $_FILES))
      {
         $result += (integer) \move_uploaded_file($_FILES[self::EXTRA_PREGAME_LUA]['tmp_name'],
                                                  "./esodata/{$this->extralang}_pregame.lua");
      }

      // Uncompress lang files.
      if (\array_key_exists(self::BASE_LANG, $_FILES))
      {
         $result += (integer) $this->unzip($_FILES[self::BASE_LANG], "./esodata/");
      }
      if (\array_key_exists(self::EXTRA_LANG, $_FILES))
      {
         $result += (integer) $this->unzip($_FILES[self::EXTRA_LANG], "./esodata/");
      }

      return $result === 6;
   }
   
   /**
    * Calls the CGI script to update the database.
    * @return int The following codes: 
    *  1 - INVALID NUMBER OF ARGUMENTS
    *  2 - INVALID DIRECTORY
    *  3 - INVALID OFFICIAL LANGUAGE
    *  6 - INVALID LANG FILE
    *  8 - INVALID CLIENT AND/OR PREGAME FILE
    *  9 - CANNOT WRITE FILES
    * 10 - SUCCESS (No error)
    */
   private function callCgi($baselang, $extralang)
   {              
      $cmd = "./cgi-bin/dumper.cgi ./esodata/ $baselang $extralang update";
      $output = [];
      return \intval(exec(escapeshellcmd($cmd), $output));
   }
   
   /**
    * Add new records to NewLang table by processing "new_lang_records_nnn.sql".
    * Files have been generated by a CGI script.
    */
   private function addNewLang()
   {
      $files = \array_diff(\scandir("./esodata"), array('..', '.'));    
      foreach ($files as $entry)
      {
         if (\preg_match('/new_lang_records_[0-9]{3}\.sql/', $entry) === 1)
         {
            self::$sqlm->multiQuery(\file_get_contents("./esodata/$entry"));
            \unlink("./esodata/$entry");
         }
      }
   }

   /**
    * Add new records to NewLua table by processing "new_lua_records.sql".
    * This file has been generated by a CGI script.
    */
   private function addNewLua()
   {
      if (\file_exists("./esodata/new_lua_records.sql"))
      {
         self::$sqlm->multiQuery(\file_get_contents("./esodata/new_lua_records.sql"));
         \unlink("./esodata/new_lua_records.sql");
      }
   }
   
   /**
    * Deletes UPDATING tasks and records that are marked as "deleted". Also, it unmarks "new" and 
    * "modified" records.
    */
   private function prepareDatabase()
   {
      // Delete UPDATING tasks.
      $taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
      $taskAssembler = new \rocinante\persistence\DomainAssembler($taskFactory);
      $taskIdentity = $taskFactory->getIdentity();
      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsIdentity = $taskContentsFactory->getIdentity();

      $taskIdentity->field("Type")->eq("UPDATING");
      $taskIdentity->join($taskContentsIdentity, "Task.TaskId", "TaskContents.TaskId");
      $taskAssembler->delete($taskIdentity, array("Task", "TaskContents"));

      // Update Lang records.
      $updateLang = "UPDATE Lang SET IsNew = 0, IsModified = 0 WHERE IsNew = 1 OR IsModified = 1";
      self::$sqlm->query($updateLang);

      // Update Lua records.
      $updateLua = "UPDATE Lua SET IsNew = 0, IsModified = 0 WHERE IsNew = 1 OR IsModified = 1";
      self::$sqlm->query($updateLua);
      
      // Delete NewLang and NewLua records.
      self::$sqlm->query("TRUNCATE NewLang");
      self::$sqlm->query("TRUNCATE NewLua");
   }
}
