<?php

namespace rocinante\command\setup;

/**
 * StartSetup sets setup language and loads it.
 */
class StartSetup extends \rocinante\controller\Command
{ 
   /**
    * Reads lang directory and finds available languages.
    */
   function doExecute()
   {
      if ($this->request->getProperty('cmd') === "setup/StartSetup")
      {
         {
            $language = $this->request->getProperty('language');
            if (!\file_exists("lang/$language/app.xml"))
            {
               throw new \Exception("Localization file was not found");
            }
            
            $file = \fopen("setup_language", "w");
            if (\fwrite($file, $language) !== false)
            {
               include "rocinante/view/setup_process.php";
            }
         }
      }
   }
}