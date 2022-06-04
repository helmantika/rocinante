<?php

namespace rocinante\command\addon;

require_once 'rocinante/command/addon/FileUtils.php';
require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * GenerateAddon dumps the English strings and the translated content of the database and create 
 * the files that are needed to make an unofficial translation for The Elder Scrolls Online.
 * The creation of the files is made by a CGI script developed in C++ because is the fastest way. 
 * PHP is not able to handle a task like this properly.
 */
class GenerateAddon extends \rocinante\controller\Command
{

   use \rocinante\command\addon\FileUtils
   {
      unzip as private;
      recursiveDelete as private;
      readAllFiles as private;
   }
   
   /**
    * Dumps the English strings and the translated content of the database and create the files that
    * are needed to make an unofficial translation for The Elder Scrolls Online.
    * The creation of the files is made by a CGI script developed in C++ because is the fastest way. 
    * PHP is not able to handle a task like this properly.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "addon/GenerateAddon")
      {
         $message = null;
         $viewhelper = \rocinante\view\ViewHelper::instance();
         $l10n = $viewhelper->getL10n();
         $mode = $this->request->getProperty('mode');
         $version = $this->request->getProperty('version');
         
         // Process extra files mode.
         if ($mode !== "NO_EXTRAFILES")
         {
            if ($mode === "DELETE_EXTRAFILES")
            {
               $this->recursiveDelete("./addon/", "./addon/");
            }

            if (isset($_FILES) && \array_key_exists(0, $_FILES))
            {
               if ($this->unzip($_FILES[0], "./addon/") === false)
               {
                  $message = \sprintf((string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"no-zip"}, \basename($_FILES[0]['name']));
               }
            }
         }
         
         if (empty($message))
         {
            // Process header files.
            if (isset($_FILES) && \array_key_exists(1, $_FILES)) // Client header
            {
               \move_uploaded_file($_FILES[1]['tmp_name'], "./esodata/client_str.txt");
            }
            if (isset($_FILES) && \array_key_exists(2, $_FILES)) // Pregame header
            {
               \move_uploaded_file($_FILES[2]['tmp_name'], "./esodata/pregame_str.txt");
            }

            // Dump the database by creating temporary files.
            $this->dumpLang();
            $this->dumpLua();

            // Generate the files for the add-on.
            $baselang = (string) $viewhelper->getConfig()->{"baselang"};
            $extralang = (string) $viewhelper->getConfig()->{"extralang"};
            $targetlang = (string) $viewhelper->getConfig()->{"targetlang"};
            $result = $this->callCgi($baselang, $extralang, $targetlang);

            // If everything is right, compress the add-on content and move it into 'download' dir.
            if ($result === 10)
            {
               // Create directories and move the files.
               if (!file_exists("./addon/EsoUI/lang") && !is_dir("./addon/EsoUI/lang"))
               {
                  \mkdir("./addon/EsoUI/lang", 0755, true);
               }
               if (!file_exists("./addon/gamedata/lang") && !is_dir("./addon/gamedata/lang"))
               {
                  \mkdir("./addon/gamedata/lang", 0755, true);
               }
               \rename("./esodata/{$targetlang}_client.str", "./addon/EsoUI/lang/{$targetlang}_client.str");
               \rename("./esodata/{$targetlang}_pregame.str", "./addon/EsoUI/lang/{$targetlang}_pregame.str");
               \rename("./esodata/{$targetlang}.lang", "./addon/gamedata/lang/{$targetlang}.lang");

               // Create the archive.
               $zip = new \ZipArchive();
               $zipname = "/tmp/" . $l10n->{"addon"};
               if (!empty($version))
               {
                  $zipname .= "-$version";
               }
               $zipname .= ".zip";
               $addonFiles = $this->readAllFiles("./addon/");
               $resource = $zip->open($zipname, \ZipArchive::CREATE);
               if ($resource === true)
               {
                  foreach($addonFiles['files'] as $file)
                  {
                     $zip->addFile($file, \substr($file, \strlen("./addon/")));
                  }
                  $zip->close();

                  // Move it into 'download' directory.
                  $this->recursiveDelete("./download/", "./download/");
                  \rename($zipname, "./download/" . \basename($zipname));
               }
            }
            // Create an error message.
            else
            {
               $error = "error$result";
               $message = (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->$error;
            }
         }
      }

      $response = array("result" => !empty($message) ? "fail" : "ok", "html" => $message);
      echo \json_encode($response);
   }

   /**
    * Calls the CGI script to generate the add-on.
    * @return int The following codes:
    *  1 - INVALID NUMBER OF ARGUMENTS
    *  2 - INVALID DIRECTORY
    *  3 - INVALID OFFICIAL LANGUAGE
    *  4 - INVALID TARGET LANGUAGE
    *  5 - INVALID TRANSLATION FILE (LANG)
    *  6 - INVALID LANG FILE
    *  7 - INVALID TRANSLATION FILE (LUA)
    *  8 - INVALID CLIENT AND/OR PREGAME FILE
    *  9 - CANNOT WRITE FILES
    * 10 - SUCCESS (No error)
    */
   private function callCgi($baselang, $extralang, $targetlang)
   {
      $url = "./cgi-bin/addon.cgi ./esodata/ ./esodata/ $baselang $extralang $targetlang";
      $output = [];
      exec(escapeshellcmd($url), $output);
      return \intval($output[0]);
   }
   
   /**
    * Dumps the translated strings from the Lang table and generates a file that will be read by a 
    * CGI script.
    *
    * The file structure is the following one:
    * {{TableId,SeqId,TextId}}
    * Translated text.
    *
    * Example:
    *
    * {{0x7c7c1e,0,968}}
    * Usando…
    *
    * {{0x7c7c1e,0,974}}
    * Abriendo el saco…
    *
    * ...
    */
   private function dumpLang()
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $file = \fopen("./esodata/db_lang.tmp", 'w' );
      
      $counterIdentity = new \rocinante\mapper\identity\Identity(array('Es' => 's'), "Lang");
      $counterIdentity->count('Es');
      $result = $assembler->find($counterIdentity)->first();
      $totalRows = \intval($result->get('COUNT(Es)'));
      
      for ($c = 0; $c < \floor($totalRows / 1000) + 1; $c++)
      {
         $identity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 'i', 'SeqId' => 'i', 'Es' => 's'), "Lang");

         // This where clause has the effect of filtering out both null and empty strings.
         $identity->field("Es")->gt("")->limit($c * 1000, 1000);
         $collection = $assembler->find($identity);
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $output = "{{0x" . \dechex(\intval($object->get('TableId'))) . "," . $object->get('SeqId') . "," . $object->get('TextId') . "}}\n";
            $output .= $object->get('Es') . "\n\n";
            \fwrite($file, $output);
         }
      }
      
      \fclose($file);
   }
   
   /**
    * Dumps the translated strings from the Lua table and generates a file that will be read by a 
    * CGI script.
    *
    * The file structure is the following one:
    * {{id,version}}
    * Translated text.
    *
    * Example:
    *
    * {{SI_ABANDON_MAIN_QUEST_FAIL,0}}
    * No puedes abandonar la misión principal.
    *
    * {{SI_ABANDON_QUEST_CONFIRM,0}}
    * Abandonar
    *
    * ...
    */
   private function dumpLua()
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 'i', 'Es' => 's'), "Lua");
      // This where clause has the effect of filtering out both null and empty strings.
      $identity->field("Es")->gt("");
      $collection = $assembler->find($identity);
      $generator = $collection->getGenerator();
      
      $file = \fopen("./esodata/db_lua.tmp", 'w' );
      foreach ($generator as $object)
      {
         $output = "{{" . $object->get('TextId') . ",0}}\n";
         $output .= $object->get('Es') . "\n\n";
         \fwrite($file, $output);
      }
      \fclose($file);
   }

}
