<?php

namespace rocinante\command\addon;

/**
 * FileUtils is trait that provides some useful functions to handle files and directories.
 */
trait FileUtils
{

   /**
    * Uncompress a ZIP archive.
    * @param array $archive A file from $_FILES.
    * @param string $to A directory where files will be extracted.
    * @return bool true on success, otherwise false.
    */
   public function unzip($archive, $to)
   {
      $result = false;
      $zip = new \ZipArchive();
      if ($zip->open($archive['tmp_name']) === true)
      {
         $zip->extractTo($to);
         $zip->close();
         $result = true;
      }

      return $result;
   }

   /**
    * Deletes a file or recursively deletes a directory.
    * @param string $str Path to file or directory.
    * @param string $preserve Specify $str again to not delete it.
    */
   public function recursiveDelete($str, $preserve = null)
   {
      if (\is_file($str))
      {
         return \unlink($str);
      } 
      elseif (\is_dir($str))
      {
         $scan = \glob(\rtrim($str, '/') . '/*');
         foreach ($scan as $index => $path)
         {
            $this->recursiveDelete($path);
         }
         if ($str !== $preserve)
         {
            return \rmdir($str);
         }
      }
   }

   /**
    * Finds path, relative to the given root folder, of all files and directories in the given 
    * directory and its sub-directories non recursively.
    * 
    * @author sreekumar
    * @param string $rootdir The starting directory.
    * @result array An array of the form array('files' => [], 'dirs' => []).
    */
   public function readAllFiles($rootdir = '.')
   {
      $files = array('files' => array(), 'dirs' => array());
      $directories = array();
      $root = $rootdir[\strlen($rootdir) - 1] == '/' ? $rootdir : $rootdir . "/";

      $directories[] = $root;

      while (\count($directories))
      {
         $dir = \array_pop($directories);
         $handle = \opendir($dir);
         if ($handle)
         {
            while (false !== ($file = \readdir($handle)))
            {
               if ($file == '.' || $file == '..')
               {
                  continue;
               }
               $file = $dir . $file;
               if (\is_dir($file))
               {
                  $directory_path = $file . "/";
                  \array_push($directories, $directory_path);
                  $files['dirs'][] = $directory_path;
               } 
               elseif (\is_file($file))
               {
                  $files['files'][] = $file;
               }
            }
            \closedir($handle);
         }
      }

      return $files;
   }

}
