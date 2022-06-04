<?php

namespace rocinante\view;

/**
 * ViewHelper manages localization data and aesthetics aspects of the application.
 */
class ViewHelper
{

   /**
    * The XML configuration file root.
    * @var \SimpleXMLElement
    */
   private $config = null;

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n = null;

   /**
    * The tips array.
    * @var array
    */
   private $tips = null;
   
   /**
    * The jQuery 'theme', 'background' color, and 'logo' used by the application.
    * @var array 
    */
   private $theme = array();

   /**
    * The one and only instance of this class.
    * @var AppRegistry
    */
   private static $instance = null;

   /**
    * LangHelper can't be instanced directly.
    */
   private function __construct()
   {
      $this->config = $this->readConfigFile();
      $this->l10n = $this->readL10nFile();
      $this->tips = $this->readTipsFile();
   }

   /**
    * Returns the one and only instance of this class.
    * @return AppRegistry The AppRegistry instance.
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
    * Loads the configuration file.
    * @return mixed If file exists, a SimpleXMLElement; false otherwise.
    */
   private function readConfigFile()
   {
      if (\file_exists("config/config.xml"))
      {
         return \simplexml_load_file("config/config.xml");
      }

      return false;
   }

   /**
    * Loads the localization file by means of language parameter that is set in configuration file.
    * @return \SimpleXMLElement A XML localization file root.
    * @throws \Exception Localization file was not found.
    */
   private function readL10nFile()
   {
      $language = isset($this->config->language) ? (string) $this->config->language : (string) \file_get_contents("setup_language");

      if (!\file_exists("lang/$language/app.xml"))
      {
         throw new \Exception("Localization file was not found");
      }

      return \simplexml_load_file("lang/$language/app.xml");
   }
   
   /**
    * Loads a localized file, that contains translation tips, by means of language parameter that 
    * is set in configuration file.
    * @return array An array where each value is a tip.
    * @throws \Exception Tips file was not found.
    */
   private function readTipsFile()
   {
      $language = isset($this->config->language) ? (string) $this->config->language : "en";

      if (!\file_exists("lang/$language/tips.php"))
      {
         throw new \Exception("Tips file was not found");
      }

      require_once "lang/$language/tips.php";
      return $GLOBALS["tips"];
   }

   /**
    * Loads a localized file, that contains explanation of ESO codes, by means of language parameter
    * that is set in configuration file.
    * @return array An array where each key is a code and its value is an explanation.
    * @throws \Exception Codes file was not found.
    */
   public function readCodesFile()
   {
      $language = isset($this->config->language) ? (string) $this->config->language : "en";

      if (!\file_exists("lang/$language/codes.php"))
      {
         throw new \Exception("Codes file was not found");
      }

      require_once "lang/$language/codes.php";
      return $GLOBALS["codes"];
   }
   
   /**
    * Loads the theme file and gets the data related to a theme that is set in configuration file.
    * @throws \Exception Theme file was not found.
    */
   public function loadThemeData($themeName = null)
   {
      if (!\file_exists("rocinante/view/themes.xml"))
      {
         throw new \Exception("Theme file was not found");
      }

      $themes = \simplexml_load_file("rocinante/view/themes.xml");
      $selected = "hot-sneaks";
      if ($themeName !== null)
      {
         $selected = $themeName;
      } 
      elseif (isset($this->config->{"jquery-theme"}))
      {
         $selected = (string) $this->config->{"jquery-theme"};
      }

      foreach ($themes->theme as $theme)
      {
         if (\trim((string) $theme['name']) === $selected)
         {
            $this->theme = array('theme' => $selected,
                                 'background' => (string) $theme->background,
                                 'logo' => (string) $theme->logo);
            break;
         }
      }
   }
   
   /**
    * Gets the XML configuration file root.
    * @return \SimpleXMLElement An XML element.
    */
   public function getConfig()
   {
      return $this->config;
   }

   /**
    * Gets the XML localization file root.
    * @return \SimpleXMLElement An XML element.
    */
   public function getL10n()
   {
      return $this->l10n;
   }

   /**
    * Gets the array that contains the translation tips.
    * @return array An array where each value is a tip.
    */
   public function getTips()
   {
      return $this->tips;
   }
   
   /**
    * Gets the jQuery theme used by the application.
    * @return string A jQuery theme. See 'themes.xml' to find out all available themes.
    */
   public function getTheme()
   {
      return $this->theme['theme'];
   }

   /**
    * Gets the color background used by the application.
    * @return string A color given as #rrggbb
    */
   public function getBackground()
   {
      return $this->theme['background'];
   }

   /**
    * Gets Rocinante's logo used by the application.
    * @return string An image.
    */
   public function getLogo()
   {
      return $this->theme['logo'];
   }

}
