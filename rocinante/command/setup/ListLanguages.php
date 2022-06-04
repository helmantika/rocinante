<?php

namespace rocinante\command\setup;

/**
 * ListLanguages returns a JSON array with languages available for Rocinante. Each key stores language
 * code and its value is the name of the language.
 */
class ListLanguages extends \rocinante\controller\Command
{
   /**
    * Reads lang directory and finds available languages.
    * @return string A JSON string.
    */
   function doExecute()
   {
      if ($this->request->getProperty('cmd') === "setup/ListLanguages")
      {
         $directories = \array_diff(\scandir("./lang"), array('..', '.'));
         foreach ($directories as $dir)
         {
            if (\is_dir("./lang/$dir") && $files = \opendir("./lang/$dir"))
            {
               while (($file = \readdir($files)) !== false)
               {
                  if ($file === "app.xml")
                  {
                     $content = \simplexml_load_file("./lang/$dir/$file");
                     $languages[(string) $content->{"id"}] = array("name" => (string) $content->{"name"},
                                                                   "button" => (string) $content->{"setup"}->{"next-button"});
                  }
               }
               \closedir($files);
            }
         }
      }

      echo \json_encode($languages);
   }
}
