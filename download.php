<?php

require_once 'rocinante/command/SessionRegistry.php';

$session = \rocinante\command\SessionRegistry::instance();
$session->resume();

$handle = \opendir("./download/");
if ($handle)
{
   while (false !== ($file = \readdir($handle)))
   {
      if ($file == '.' || $file == '..')
      {
         continue;
      }
      
      $file = "./download/" . $file;
      if (\is_file($file))
      {
         \header('Content-Description: File Transfer');
         \header('Content-Type: application/octet-stream');
         \header('Content-Disposition: attachment; filename="' . \basename($file) . '"');
         \header('Expires: 0');
         \header('Cache-Control: must-revalidate');
         \header('Pragma: public');
         \header('Content-Length: ' . \filesize($file));
         \readfile($file);
         exit;
      }
   }
}

