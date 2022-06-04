<?php

namespace rocinante\command\setup;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/persistence/SqlImport.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/controller/Request.php';
require_once 'rocinante/command/addon/FileUtils.php';
require_once 'rocinante/command/translation/UpdateEsoTableSize.php';
require_once 'rocinante/command/translation/UpdateEsoTablePercentages.php';

/**
 * PopulateDatabase prepares Rocinante for working. In other words, it adds strings to be translated
 * from a set of lang and LUA files, it creates metatables, and it updates translation status.
 */
class PopulateDatabase extends \rocinante\controller\Command
{
   const BASE_CLIENT_LUA = 0;
   const BASE_PREGAME_LUA = 1;
   const BASE_LANG = 2;
   const EXTRA_CLIENT_LUA = 3;
   const EXTRA_PREGAME_LUA = 4;
   const EXTRA_LANG = 5;
   const CSV_FILE = 6;

   use \rocinante\command\addon\FileUtils
   {
      unzip as private;
   }
   
   use \rocinante\persistence\SqlImport
   {
      sqlImport as private;
      removeComments as private;
      isQuoted as private;
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
    * The target language.
    * @var string
    */
   private $targetlang;
   
   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;
   
   /**
    * The Lang persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $langFactory = null;

   /**
    * The Lang object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $langAssembler = null;

   /**
    * The Lua persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $luaFactory = null;

   /**
    * The Lua object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $luaAssembler = null;

   /**
    * The EsoTable persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $esoTableFactory = null;

   /**
    * The EsoTable object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $esoTableAssembler = null;

   /**
    * The update consists of a lot of steps. The hard work is done by a CGI script.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "setup/PopulateDatabase")
      {
         $message = null;
         $this->createConfigFile("config/config.xml");
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $this->targetlang = $this->request->getProperty('targetlang');
                  
         if (isset($this->targetlang) && \strlen($this->targetlang) === 2)
         {
            if (isset($_FILES) && \count($_FILES) >= 6)
            {
               self::$sqlm = \rocinante\persistence\SqlManager::instance();
               $viewhelper = \rocinante\view\ViewHelper::instance();
               $this->baselang = (string) $viewhelper->getConfig()->{"baselang"};
               $this->extralang = (string) $viewhelper->getConfig()->{"extralang"};

               // Handle files.
               $this->uploadNewFiles();

               // Call the CGI.
               $result = $this->callCgi($this->baselang, $this->extralang);
               if ($result === 10)
               {
                  $this->langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
                  $this->langAssembler = new \rocinante\persistence\DomainAssembler($this->langFactory);
                  $this->luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
                  $this->luaAssembler = new \rocinante\persistence\DomainAssembler($this->luaFactory);
                  $this->esoTableFactory = new \rocinante\persistence\PersistenceFactory("EsoTable");
                  $this->esoTableAssembler = new \rocinante\persistence\DomainAssembler($this->esoTableFactory);

                  // Populate the database.     
                  $files = \array_diff(\scandir("./esodata"), array('..', '.'));    
                  foreach ($files as $entry)
                  {
                     if (\preg_match('/new_lang_records_[0-9]{3}\.sql/', $entry) === 1 ||
                         $entry === "new_lua_records.sql")
                     {
                        self::$sqlm->multiQuery(\file_get_contents("./esodata/$entry"));
                        \unlink("./esodata/$entry");
                     }
                  }
   
                  // Add new ESO tables.
                  $this->addTables();
                  $this->addMetatables();
                  
                  // Add term and string types.
                  $this->addTermTypes();
                  $this->addLangTypes();
                  
                  // Update stats of ESO tables.
                  $this->updateEsoTableStats();
                  
                  // Update main status.
                  $this->updateStatus();
                  
                  // Set flag for target language.
                  $this->createFlag();
                  
                  // Secure config folder.
                  chmod("config", 0700);
               } 
               else
               {             
                  // Report the error.
                  $error = "error$result";
                  $message = (string) $this->l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->$error;
               }
            }
            else
            {
               // Report the error.
               $error = "error0";
               $message = (string) $this->l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->$error;
            }
         }
         else
         {
            $message = (string) $this->l10n->{"setup"}->{"target-language-error"};
         }
      }
      
      $response = array("result" => !empty($message) ? "null" : "ok", "html" => $message);
      echo \json_encode($response);
   }

   /**
    * Upload new LUA and lang files into "esodata" folder. 
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
      // Uncompress CSV file.
      if (\array_key_exists(self::CSV_FILE, $_FILES))
      {
         $zip = new \ZipArchive();
         if ($zip->open($_FILES[self::CSV_FILE]['tmp_name']) === true)
         {
            // Only one file is expected.
            $zip->extractTo("./esodata/", array($zip->getNameIndex(0)));
            \rename("./esodata/" . $zip->getNameIndex(0), "./esodata/target.lang.csv");
            $zip->close();
            $result++;
         }
      }
      
      return $result >= 6;
   }

   /**
    * Calls the CGI script to update the database.
    * @return int The following codes:
    *  1 - INVALID NUMBER OF ARGUMENTS
    *  2 - INVALID DIRECTORY
    *  3 - INVALID OFFICIAL LANGUAGE
    *  8 - INVALID CLIENT AND/OR PREGAME FILE
    *  9 - CANNOT WRITE FILES
    * 10 - SUCCESS (No error)
    */
   private function callCgi($baselang, $extralang)
   {
      $cmd = "./cgi-bin/dumper.cgi ./esodata/ $baselang $extralang setup";
      $output = [];
      return \intval(exec(escapeshellcmd($cmd), $output));
   }

   /**
    * Add new ESO tables.
    * @todo Hard-coded data should be read from a file.
    */
   private function addTables()
   {
      // Insert entries for LUA table and metatables.
      $newTable = (string) $this->l10n->frontpage->tabs->{"master-table"}->{"new-table"};
      $insert  = "INSERT INTO `EsoTable` (`TableId`, `Number`, `Description`, `TypeId`) VALUES";
      $insert .= "(0, NULL, '$newTable', 2),";
      $insert .= "(1, NULL, '$newTable', 1),";
      $insert .= "(2, NULL, '$newTable', 1),";
      $insert .= "(3, NULL, '$newTable', 1),";
      $insert .= "(4, NULL, '$newTable', 9);";
      self::$sqlm->query($insert);
      self::$sqlm->close($insert);
      
      // Insert data for lang tables.
      $query = "SELECT TableId FROM Lang GROUP BY TableId ORDER BY TableId";
      self::$sqlm->query($query);
      while ($row = self::$sqlm->fetchAssoc($query))
      {
         $raw[] = $row;
      }
      self::$sqlm->close($query);

      if (isset($raw))
      {
         $lastTable = 1;

         // Create every new table.
         foreach ($raw as $table)
         {
            $esoTable = $this->esoTableFactory->getDomainFactory()->createObject(array());
            $esoTable->set('TableId', $table['TableId']);
            $esoTable->set('Number', $lastTable++);
            $esoTable->set('Description', $newTable);
            $this->esoTableAssembler->insert($esoTable);
         }
         
         // Update type for tables that are part of a metatable.
         $query  = "UPDATE `EsoTable` SET `TypeId`=3 WHERE `TableId`=200879108;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=4 WHERE `TableId`=204987124;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=4 WHERE `TableId`=228103012;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=3 WHERE `TableId`=3952276;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=4 WHERE `TableId`=20958740;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=4 WHERE `TableId`=249936564;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=5 WHERE `TableId`=265851556;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=7 WHERE `TableId`=228378404;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=7 WHERE `TableId`=242841733;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=9 WHERE `TableId`=21337012;";
         $query .= "UPDATE `EsoTable` SET `TypeId`=9 WHERE `TableId`=51188213;";
         self::$sqlm->multiQuery($query);
      }
   }

   /**
    * Populates the table that stores the relationship between simple tables.
    * @todo Hard-coded data should be read from a file.
    */
   private function addMetatables()
   {
      $query  = "INSERT INTO `MetaTable` (`MetaTableId`, `Seq`, `TableId`) VALUES";
      $query .= "(1, 2, 200879108),";
      $query .= "(1, 3, 204987124),";
      $query .= "(1, 1, 228103012),";
      $query .= "(2, 2, 3952276),";
      $query .= "(2, 3, 20958740),";
      $query .= "(2, 1, 249936564),";
      $query .= "(2, 4, 265851556),";
      $query .= "(3, 2, 228378404),";
      $query .= "(3, 1, 242841733),";
      $query .= "(4, 2, 21337012),";
      $query .= "(4, 1, 51188213);";
      self::$sqlm->query($query);
      self::$sqlm->close($query);
   }
   
   /**
    * Populates the table that stores glossary term types.
    */
   private function addTermTypes()
   {
      $type = $this->l10n->setup->{"term-type"};
      $query  = "INSERT INTO `TermType` (`TypeId`, `Description`) VALUES";
      $query .= "(1, '" . (string) $type->{"t1"} . "'),";
      $query .= "(2, '" . (string) $type->{"t2"} . "'),";
      $query .= "(3, '" . (string) $type->{"t3"} . "'),";
      $query .= "(4, '" . (string) $type->{"t4"} . "'),";
      $query .= "(5, '" . (string) $type->{"t5"} . "'),";
      $query .= "(6, '" . (string) $type->{"t6"} . "');";
      self::$sqlm->query($query);
      self::$sqlm->close($query);
   }
   
   /**
    * Populates the table that stores string types.
    */
   private function addLangTypes()
   {
      $type = $this->l10n->setup->{"lang-type"};
      $query  = "INSERT INTO `LangType` (`TypeId`, `Description`, `Color`) VALUES";
      $query .= "(0, '" . (string) $type->{"t0"} . "','#adb3b3'),";
      $query .= "(1, '" . (string) $type->{"t1"} . "','#ceb48f'),";
      $query .= "(2, '" . (string) $type->{"t2"} . "','#fbd542'),";
      $query .= "(3, '" . (string) $type->{"t3"} . "','#87b8e0'),";
      $query .= "(4, '" . (string) $type->{"t4"} . "','#a2ceb7'),";
      $query .= "(5, '" . (string) $type->{"t5"} . "','#b175b1'),";
      $query .= "(6, '" . (string) $type->{"t6"} . "','#f48250'),";
      $query .= "(7, '" . (string) $type->{"t7"} . "','#9598cb'),";
      $query .= "(8, '" . (string) $type->{"t8"} . "','#e56d6c'),";
      $query .= "(9, '" . (string) $type->{"t9"} . "','#2471b7');";
      
      self::$sqlm->query($query);
      self::$sqlm->close($query);
   }
   
   /**
    * Updates size, percentage of translated strings, and percentage of revised strings of every 
    * ESO table.
    */
   private function updateEsoTableStats()
   {
      $request1 = new \rocinante\controller\Request();
      $request1->setProperty('cmd', 'translation/UpdateEsoTableSize');
      $command1 = new \rocinante\command\translation\UpdateEsoTableSize();
      $command1->execute($request1);
      
      $request2 = new \rocinante\controller\Request();
      $request2->setProperty('cmd', 'translation/UpdateEsoTablePercentages');
      $command2 = new \rocinante\command\translation\UpdateEsoTablePercentages();
      $command2->execute($request2);
   }
   
   /**
    * Counts the number of strings, the number of translated strings, and calculates their 
    * ratio.
    */
   private function updateStatus()
   {
      $counter1 = new \rocinante\mapper\identity\Identity(array('TextId' => 's'), "Lua");
      $counter1->count("TextId");
      $object1 = $this->luaAssembler->find($counter1)->first();
      $luaSize = \intval($object1->get('COUNT(TextId)'));
      
      $counter2 = new \rocinante\mapper\identity\Identity(array('TextId' => 'i'), "Lang");
      $counter2->count("TextId");
      $object2 = $this->langAssembler->find($counter2)->first();
      $langSize = \intval($object2->get('COUNT(TextId)'));
      
      $counter3 = new \rocinante\mapper\identity\Identity(array('TextId' => 's', 'IsTranslated' => 'i'), "Lua");
      $counter3->count("TextId")->field("IsTranslated")->eq(1);
      $object3 = $this->luaAssembler->find($counter3)->first();
      $translatedLua = \intval($object3->get('COUNT(TextId)'));
      
      $counter4 = new \rocinante\mapper\identity\Identity(array('TextId' => 'i', 'IsTranslated' => 'i'), "Lang");
      $counter4->count("TextId")->field("IsTranslated")->eq(1);
      $object4 = $this->langAssembler->find($counter4)->first();
      $translatedLang = \intval($object4->get('COUNT(TextId)'));
      
      $size = $luaSize + $langSize;
      $translated = $translatedLua + $translatedLang;
      $percentage = (double) $translated * 100.0 / (double) $size;
      
      $factory = new \rocinante\persistence\PersistenceFactory("Status");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $identity->field("StatusId")->eq(0);
      $collection = $assembler->find($identity);
      $row = $collection->first();
      if ($row !== null)
      {
         $row->set('Total', $size);
         $row->set('Translated', $translated);
         $row->set('Percentage', $percentage);
         $assembler->update($row);
      }
   }
   
   /**
    * Creates an XML configuration file for Rocinante. It stores first, second, and target language;
    * jQuery UI theme, and Rocinante's language.
    * @param string $filename A configuration file name plus with a relative path.
    */
   private function createConfigFile($filename)
   {
      $xml  = "<?xml version='1.0' encoding='UTF-8'?>" . PHP_EOL;
      $xml .= "<options>" . PHP_EOL;
      $xml .= "   <!-- Rocinante's language. Files with this ID will be read from lang folder: ID.xml, codes-ID.xml," . PHP_EOL;
      $xml .= "   and tips-ID.xml -->" . PHP_EOL;
      $xml .= "   <language>" . \file_get_contents("setup_language") . "</language>" . PHP_EOL;
      $xml .= "   <!-- A jQuery UI theme to be selected from the following ones: ui-lightness, ui-darkness, " . PHP_EOL;
      $xml .= "   smoothness, start, redmond, sunny, overcast, le-frog, flick, pepper-grinder, eggplant, dark-hive," . PHP_EOL;
      $xml .= "   cupertino, south-street, blitzer, humanity, hot-sneaks, excite-bike, vader, dot-luv, mint-choc, " . PHP_EOL;
      $xml .= "   black-tie, trontastic, and swanky-purse." . PHP_EOL;
      $xml .= "   For further information, visit http://jqueryui.com/themeroller/ -->" . PHP_EOL;
      $xml .= "   <jquery-theme>hot-sneaks</jquery-theme>" . PHP_EOL;
      $xml .= "   <!-- An official language supported by the game that will be used as foundation to generate the " . PHP_EOL;
      $xml .= "   add-on. The first thought to select one usually is English. However, another option can be a " . PHP_EOL;
      $xml .= "   better choice. For instance, French is better for Spanish because both are romantic languages. " . PHP_EOL;
      $xml .= "   The allowed values are en, fr, or de. -->" . PHP_EOL;
      $xml .= "   <baselang>fr</baselang>" . PHP_EOL;
      $xml .= "   <!-- A second official language supported by the game and used by Rocinante. -->" . PHP_EOL;
      $xml .= "   <extralang>en</extralang>" . PHP_EOL;
      $xml .= "   <!-- The language the game will be translated into -->" . PHP_EOL;
      $xml .= "   <targetlang>" . $this->request->getProperty('targetlang') . "</targetlang>" . PHP_EOL;
      $xml .= "</options>";
      
      $file = \fopen($filename, "w");
      \fwrite($file, $xml);
      
      // Remove temp file.
      \unlink("setup_language");
   }
   
   /**
    * Sets a flag for the target language. If language is unknown, a generic flag will be set.
    */
   private function createFlag()
   {
      if(\copy( "images/flags/" . $this->request->getProperty('targetlang') . ".png", "config/target.png") === false)
      {
         \copy("images/flags/un.png", "config/target.png");
      }
   }
}
