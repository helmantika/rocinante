<?php

namespace rocinante\persistence;

/**
 * SqlImport is a trait that provides a useful function to import lots of SQL queries.
 * 
 * @author Gromo <https://stackoverflow.com/users/2159497/gromo>
 */
trait SqlImport
{
   /**
    * Import SQL from a string array.
    */
   private function sqlImport($mysqli, $array)
   {
      $delimiter = ';';
      $isFirstRow = true;
      $isMultiLineComment = false;
      $sql = '';

      foreach ($array as $row)
      {
         // Remove BOM for UTF-8 encoded file.
         if ($isFirstRow)
         {
            $row = \preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $row);
            $isFirstRow = false;
         }

         // 1. Ignore empty string and comment row.
         if (\trim($row) == '' || \preg_match('/^\s*(#|--\s)/sUi', $row))
         {
            continue;
         }

         // 2. Clear comments.
         $row = \trim($this->removeComments($row, $isMultiLineComment));

         // 3. Parse delimiter row.
         if (\preg_match('/^DELIMITER\s+[^ ]+/sUi', $row))
         {
            $delimiter = \preg_replace('/^DELIMITER\s+([^ ]+)$/sUi', '$1', $row);
            continue;
         }

         // 4. Separate sql queries by delimiter.
         $offset = 0;
         while (\strpos($row, $delimiter, $offset) !== false)
         {
            $delimiterOffset = \strpos($row, $delimiter, $offset);
            if ($this->isQuoted($delimiterOffset, $row))
            {
               $offset = $delimiterOffset + \strlen($delimiter);
            }
            else
            {
               $sql = \trim($sql . ' ' . \trim(\substr($row, 0, $delimiterOffset)));
               if ($mysqli->query($sql) !== false)
               {
                  $row = \substr($row, $delimiterOffset + \strlen($delimiter));
                  $offset = 0;
                  $sql = '';
               }
               else
               {
                  return false;
               }
            }
         }
         $sql = \trim($sql . ' ' . $row);
      }

      if (\strlen($sql) > 0)
      {
         return $mysqli->query($row) !== false;
      }

      return true;
   }

   /**
    * Removes comments from a SQL sentence.
    *
    * @param string $sql A SQL sentence.
    * @param boolean $isMultiComment is multicomment line.
    * @return string The sentence with no comments.
    */
   private function removeComments($sql, &$isMultiComment)
   {
      if ($isMultiComment)
      {
         if (\preg_match('#\*/#sUi', $sql))
         {
            $sql = \preg_replace('#^.*\*/\s*#sUi', '', $sql);
            $isMultiComment = false;
         }
         else
         {
            $sql = '';
         }
         if (\trim($sql) == '')
         {
            return $sql;
         }
      }

      $offset = 0;
      $matches = array();
      while (\preg_match('{--\s|#|/\*[^!]}sUi', $sql, $matches, \PREG_OFFSET_CAPTURE, $offset))
      {
         list($comment, $foundOn) = $matches[0];
         if ($this->isQuoted($foundOn, $sql))
         {
            $offset = $foundOn + \strlen($comment);
         }
         else
         {
            if (\substr($comment, 0, 2) == '/*')
            {
               $closedOn = \strpos($sql, '*/', $foundOn);
               if ($closedOn !== false)
               {
                  $sql = \substr($sql, 0, $foundOn) . \substr($sql, $closedOn + 2);
               }
               else
               {
                  $sql = \substr($sql, 0, $foundOn);
                  $isMultiComment = true;
               }
            }
            else
            {
               $sql = \substr($sql, 0, $foundOn);
               break;
            }
         }
      }
      return $sql;
   }

   /**
    * Checks if "offset" position is quoted.
    */
   private function isQuoted($offset, $text)
   {
      if ($offset > \strlen($text))
      {
         $offset = \strlen($text);
      }

      $isQuoted = false;
      for ($i = 0; $i < $offset; $i++)
      {
         if ($text[$i] == "'")
         {
            $isQuoted = !$isQuoted;
         }
         if ($text[$i] == "\\" && $isQuoted)
         {
            $i++;
         }
      }
      return $isQuoted;
   }
}