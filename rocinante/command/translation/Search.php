<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/SqlManager.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * Search creates an HTML table that shows matches for a given search.
 */
class Search extends \rocinante\controller\Command
{

   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager
    */
   private static $sqlm;

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * Creates an HTML table that shows strings to translate for a given ESO table.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/Search")
      {
         self::$sqlm = \rocinante\persistence\SqlManager::instance();
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $text = $this->request->getProperty('text');

         // Don't search for strings that have 3 characters or less.
         if (\strlen($text) > 3)
         {
            $page = $totalPages = 0;
            $prefix = $this->request->getProperty('table');
            $text = \str_replace("'", "\'", $text);
            
            if ($prefix === "Lang")
            {
               $generator = $this->searchLang($text, $page, $totalPages);   
            }
            else
            {
               $generator = $this->searchLua($text, $page, $totalPages);
            }
            
            if ($totalPages > 0)
            {
               $html = $this->header($prefix);
               foreach ($generator as $object)
               {
                  $html .= "<tr>\n";
                  $html .= "<td style='text-align: right' rowspan=3>0x" . \dechex($object["TableId"]) . "</td>\n";
                  if ($prefix === "Lang")
                  {
                     $html .= "<td style='text-align: right' rowspan=3>" . $object["TextId"] . "</td>\n";
                  }
                  else
                  {
                     $html .= "<td style='text-align: left' rowspan=3>" . \str_replace("_"," ", $object["TextId"]) . "</td>\n";
                  }
                  $html .= "<td style='text-align: right' rowspan=3>" . ($prefix === "Lang" ? $object["SeqId"] : "0") . "</td>\n";
                  $html .= "<td rowspan=3>" . $object['TypeId'] . "</td>\n";
                  $html .= "<td class='fr'></td>";
                  $html .= "<td>" . $this->mark($text, \nl2br(\htmlspecialchars($object["Fr"], ENT_COMPAT | ENT_HTML5, "UTF-8"))) . "</td>\n";
                  $html .= "<td rowspan=3></td>\n"; // Status color.
                  $html .= "<td rowspan=3><div>" . \nl2br(\htmlspecialchars($object["Notes"], ENT_COMPAT | ENT_HTML5, "UTF-8")) . "</div></td>\n";
                  $html .= "<td rowspan=3>0</td>\n"; // IsUpdated (only for Glossary and Updating tasks).
                  $html .= "<td rowspan=3>" . $object["IsTranslated"] . "</td>\n";
                  $html .= "<td rowspan=3>" . $object["IsRevised"] . "</td>\n";
                  $html .= "<td rowspan=3>" . $object["IsLocked"] . "</td>\n";
                  $html .= "<td rowspan=3>" . $object["IsDisputed"] . "</td>\n";
                  $html .= "</tr>\n";
                  $html .= "<tr>\n";
                  $html .= "<td class='en'></td>";
                  $html .= "<td>" . $this->mark($text, \nl2br(\htmlspecialchars($object["En"], ENT_COMPAT | ENT_HTML5, "UTF-8"))) . "</td>\n";
                  $html .= "</tr>\n";
                  $html .= "<tr>\n";
                  $html .= "<td class='es'></td>";
                  $translation = $object["Es"];
                  $html .= "<td><div>" . ($translation === null ? "<br />" : $this->mark($text, \nl2br(\htmlspecialchars($translation, ENT_COMPAT | ENT_HTML5, "UTF-8")))) . "</div></td>\n";
                  $html .= "</tr>\n";
               }
               $html .= "</tbody>\n";
               $html .= "</table>\n";
            }
            else
            {
               $html  = "<table><tr><td class='ui-widget-content' style='text-align: center'>";
               $html .= \sprintf((string) $this->l10n->{"frontpage"}->{"search-not-found"}, $text);
               $html .= "</td></tr></table>\n";
               $page = $totalPages = 0;
            }
         }
         else
         {
            $html  = "<table><tr><td class='ui-widget-content' style='text-align: center'>";
            $html .= (string) $this->l10n->{"frontpage"}->{"search-error"};
            $html .= "</td></tr></table>\n";
            $page = $totalPages = 0;
         }

         $response = array("page" => $page, "total" => $totalPages, "html" => $html);
         echo \json_encode($response);
      }
   }

   /**
    * Creates an HTML table header for a translation table.
    * @return string HTML Code.
    */
   private function header($prefix)
   {
      $caption = $this->l10n->{"frontpage"}->{"tabs"}->{"work"}->{"table"};
      $html  = "<table>\n";
      $html .= "<thead>\n";
      $html .= "<tr>\n";
      $html .= "<th style='text-align: right'>" . $caption->{"tableid"} . "</th>\n";
      $html .= "<th style='text-align: " . ($prefix === "Lang" ? "right" : "left") . "'>" . $caption->{"stringid"} . "</th>\n";
      $html .= "<th style='text-align: right'>" . $caption->{"seqid"} . "</th>\n";
      $html .= "<th>" . $caption->{"string-type"} . "</th>\n";
      $html .= "<th></th>\n";
      $html .= "<th>" . $caption->{"string"} . "</th>\n";
      $html .= "<th>" . $caption->{"status"} . "</th>\n";
      $html .= "<th>" . $caption->{"notes"} . "</th>\n";
      $html .= "<th></th>\n";
      $html .= "<th></th>\n";
      $html .= "<th></th>\n";
      $html .= "<th></th>\n";
      $html .= "<th></th>\n";
      $html .= "</tr>\n";
      $html .= "</thead>\n";
      $html .= "<tbody>\n";
      return $html;
   }

   /**
    * Search for text in Lang table.
    * @param string $text String to be searched.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data.
    */
   private function searchLang($text, &$page, &$totalPages)
   {
      $page = $totalPages = 0;

      // First try.
      $counterQuery = "SELECT COUNT(*) FROM LangSearch WHERE En LIKE '%$text%' OR Fr LIKE '%$text%' OR Es LIKE '%$text%'";
      $totalRows = $this->countMatches($counterQuery);
      if ($totalRows > 0)
      {
         $query = "SELECT Lang.*, EsoTable.TypeId FROM LangSearch
                   JOIN EsoTable ON LangSearch.TableId = EsoTable.TableId
                   JOIN Lang ON LangSearch.TableId = Lang.TableId AND LangSearch.TextId = Lang.TextId AND LangSearch.SeqId = Lang.SeqId
                   WHERE LangSearch.En LIKE '%$text%' OR LangSearch.Fr LIKE '%$text%' OR LangSearch.Es LIKE '%$text%'
                   ORDER BY LangSearch.TextId, LangSearch.SeqId";
         
         return $this->selectMatches($query, $totalRows, $page, $totalPages);
      }
      else
      {
         // Second try
         $counterQuery = "SELECT COUNT(*) FROM LangSearch WHERE MATCH (Fr,En,Es) AGAINST ('\"$text\"' IN BOOLEAN MODE)";
         $totalRows = $this->countMatches($counterQuery);
         if ($totalRows > 0)
         {
            $query = "SELECT Lang.*, EsoTable.TypeId FROM LangSearch
                      JOIN EsoTable ON LangSearch.TableId = EsoTable.TableId
                      JOIN Lang ON LangSearch.TableId = Lang.TableId AND LangSearch.TextId = Lang.TextId AND LangSearch.SeqId = Lang.SeqId
                      WHERE MATCH (LangSearch.Fr, LangSearch.En, LangSearch.Es) AGAINST ('\"$text\"' IN BOOLEAN MODE)
                      ORDER BY LangSearch.TextId, LangSearch.SeqId";
            
            return $this->selectMatches($query, $totalRows, $page, $totalPages);
         }
      }
   }

   /**
    * Search for text in Lua table.
    * @param string $text String to be searched.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data.
    */
   private function searchLua($text, &$page, &$totalPages)
   {
      // First try.
      $counterQuery = "SELECT COUNT(*) FROM LuaSearch WHERE En LIKE '%$text%' OR Fr LIKE '%$text%' OR Es LIKE '%$text%'";
      $totalRows = $this->countMatches($counterQuery);
      if ($totalRows > 0)
      {
         $query = "SELECT Lua.*, EsoTable.TypeId FROM LuaSearch
                   JOIN EsoTable ON LuaSearch.TableId = EsoTable.TableId
                   JOIN Lua ON LuaSearch.TableId = Lua.TableId AND LuaSearch.TextId = Lua.TextId
                   WHERE LuaSearch.En LIKE '%$text%' OR LuaSearch.Fr LIKE '%$text%' OR LuaSearch.Es LIKE '%$text%'
                   ORDER BY LuaSearch.TextId";
         
         return $this->selectMatches($query, $totalRows, $page, $totalPages);
      }
      else
      {
         // Second try
         $counterQuery = "SELECT COUNT(*) FROM LuaSearch WHERE MATCH (Fr,En,Es) AGAINST ('\"$text\"' IN BOOLEAN MODE)";
         $totalRows = $this->countMatches($counterQuery);
         if ($totalRows > 0)
         {
         $query = "SELECT Lua.*, EsoTable.TypeId FROM LuaSearch
                   JOIN EsoTable ON LuaSearch.TableId = EsoTable.TableId
                   JOIN Lua ON LuaSearch.TableId = Lua.TableId AND LuaSearch.TextId = Lua.TextId
                   WHERE MATCH (LuaSearch.Fr, LuaSearch.En, LuaSearch.Es) AGAINST ('\"$text\"' IN BOOLEAN MODE)
                   ORDER BY LuaSearch.TextId";
            
            return $this->selectMatches($query, $totalRows, $page, $totalPages);
         }
      }
   }
   
   /**
    * Executes a query that counts how many rows match the search.
    * @return string $counterQuery The query to count how many rows match.
    */
   private function countMatches($counterQuery)
   {
      self::$sqlm->query($counterQuery);
      $row = self::$sqlm->fetchAssoc($counterQuery);
      self::$sqlm->close($counterQuery);
      return \intval($row['COUNT(*)']);
   }
   
   
   /**
    * Executes a query that selects the rows match the search.
    * @return int $totalRows The number of rows match the search.
    */
   private function selectMatches($query, $totalRows, &$page, &$totalPages)
   {
      $rpp = \intval($this->request->getProperty('rpp'));
      $totalPages = (int) \ceil($totalRows / $rpp);
      $page = \intval($this->request->getProperty('page'));
      $startingLimit = ($page - 1) * $rpp;

      $query .= " LIMIT $startingLimit,$rpp";
      self::$sqlm->query($query);
      
      while ($row = self::$sqlm->fetchAssoc($query))
      {
         $raw[] = $row;
      }
      self::$sqlm->close($query);

      return $this->createGenerator($raw);
   }
   
   /**
    * When it is called, it returns an array that can be iterated over.
    * @param array An array.
    */
   private function createGenerator(array &$rows)
   {
      for ($x = 0; $x < \count($rows); $x++)
      {
         yield($rows[$x]);
      }
   }
   
   /**
    * Surrounds with <strong></strong> every ocurrence of a given string in a given text.
    * @return string The marked text.
    */
   private function mark($string, $text)
   {
      $offset = 0;
      $len = \strlen($string);
      $pos = stripos($text, $string);
      while($pos !== false)
      {
         $t = substr($text, $pos, $len);
         $text = substr_replace($text, "<strong>$t</strong>", $pos, $len);
         $offset = $pos + $len + 15;
         $pos = stripos($text, $string, $offset);
      }
      return $text;
   }

}
