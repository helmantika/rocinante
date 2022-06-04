<?php

namespace rocinante\command;

require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * GetAddonFileInfo reads and returns name and creation date from file located at 'download' directory.
 */
class GetAddonFileInfo extends \rocinante\controller\Command
{
   /**
    * Reads info (name and creation date) from file located at 'download' directory.
    * @return string A localized string.
    */
   function doExecute()
   {
      if ($this->request->getProperty('cmd') === "GetAddonFileInfo")
      {
         $result = null;
         $dir = \opendir("./download/");
         if ($dir !== false)
         {
            while (($file = \readdir($dir)) !== false)
            {
               if ($file !== "." && $file !== "..")
               {
                  $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
                  $mtime = \filemtime("./download/" . $file);
                  $date = \date((string) $l10n->{"format"}->{"datetime-format"} . ".", $mtime);
                  $result = sprintf((string) $l10n->{"addon-file-info"}, $file, $date);
               }
            }
            \closedir($dir);
         }
         
         echo $result;
      }
   }
}
