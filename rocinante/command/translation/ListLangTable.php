<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/translation/MetaTable.php';

/**
 * ListLangTable creates an HTML table that shows strings to translate for a given ESO table.
 */
class ListLangTable extends \rocinante\controller\Command
{
   use \rocinante\command\translation\MetaTable
   {
      readTables as protected;
   }
   
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
      if ($this->request->getProperty('cmd') === "translation/ListLangTable")
      {
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $tableid = \intval($this->request->getProperty('tableid'));
         $page = $totalPages = 0;        

         if ($tableid == 0)
         {
            $generator = $this->readLua($page, $totalPages);
            $prefix = "Lua";
         }
         else if ($tableid < 0xff)
         {
            $generator = $this->readMeta($tableid, $page, $totalPages);
            $prefix = "Lang";
         }
         else
         {
            $generator = $this->readLang($tableid, $page, $totalPages);
            $prefix = "Lang";
         }
         
         $html = $this->header($prefix);
         foreach ($generator as $object)
         {
            $html .= "<tr>\n";
            $html .= "<td style='text-align: right' rowspan=3>0x" . \dechex($object->get("$prefix.TableId")) . "</td>\n";
            if ($prefix === "Lang")
            {
               $html .= "<td style='text-align: right' rowspan=3>" . $object->get("Lang.TextId") . "</td>\n";
            }
            else
            {
               $html .= "<td style='text-align: left' rowspan=3>" . \str_replace("_"," ", $object->get("$prefix.TextId")) . "</td>\n";
            }
            $html .= "<td style='text-align: right' rowspan=3>" . ($prefix === "Lang" ? $object->get("Lang.SeqId") : "0") . "</td>\n";
            $html .= "<td rowspan=3>" . $object->get('EsoTable.TypeId') . "</td>\n";
            $html .= "<td class='fr'></td>";
            $html .= "<td>" . \nl2br(\htmlspecialchars($object->get("$prefix.Fr"), ENT_COMPAT | ENT_HTML5, "UTF-8")) . "</td>\n";
            $html .= "<td rowspan=3></td>\n"; // Status color.
            $html .= "<td rowspan=3><div>" . \nl2br(\htmlspecialchars($object->get("$prefix.Notes"), ENT_COMPAT | ENT_HTML5, "UTF-8")) . "</div></td>\n";
            $html .= "<td rowspan=3>0</td>\n"; // IsUpdated (only for Glossary and Updating tasks).
            $html .= "<td rowspan=3>" . $object->get("$prefix.IsTranslated") . "</td>\n";
            $html .= "<td rowspan=3>" . $object->get("$prefix.IsRevised") . "</td>\n";
            $html .= "<td rowspan=3>" . $object->get("$prefix.IsLocked") . "</td>\n";
            $html .= "<td rowspan=3>" . $object->get("$prefix.IsDisputed") . "</td>\n";
            $html .= "</tr>\n";
            $html .= "<tr>\n";
            $html .= "<td class='en'></td>";
            $html .= "<td>" . \nl2br(\htmlspecialchars($object->get("$prefix.En"), ENT_COMPAT | ENT_HTML5, "UTF-8")) . "</td>\n";
            $html .= "</tr>\n";
            $html .= "<tr>\n";
            $html .= "<td class='es'></td>";
            $translation = $object->get("$prefix.Es");
            $html .= "<td><div>" . ($translation === null ? "<br />" : \nl2br(\htmlspecialchars($translation, ENT_COMPAT | ENT_HTML5, "UTF-8"))) . "</div></td>\n";
            $html .= "</tr>\n";
         }
         $html .= "</tbody>\n";
         $html .= "</table>\n";
         
         
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
    * Reads contents of Lua table.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data. 
    */
   private function readLua(&$page, &$totalPages)
   {
      $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($luaFactory);

      $luaCounter = new \rocinante\mapper\identity\Identity(array('TextId' => 's'), "Lua");
      $luaCounter->count("TextId");
      $object = $assembler->find($luaCounter)->first();
      $totalRows = \intval($object->get('COUNT(TextId)'));
      $rpp = \intval($this->request->getProperty('rpp'));
      $totalPages = (int) \ceil($totalRows / $rpp);
      $page = \intval($this->request->getProperty('page'));

      $luaIdentity = $luaFactory->getIdentity();
      $luaIdentity->orderByAsc("TextId")->limit(($page - 1) * $rpp, $rpp);
      $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TypeId' => 'i'), "EsoTable");
      $luaIdentity->join($esoTableIdentity, "TableId", "TableId");
      $collection = $assembler->find($luaIdentity);
      return $collection->getGenerator();
   }
   
   /**
    * Reads contents of Lang table.
    * @param type $tableid Table ID.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data. 
    */
   private function readLang($tableid, &$page, &$totalPages)
   {
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($langFactory);

      $langCounter = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "Lang");
      $langCounter->count("TableId")->field("TableId")->eq($tableid);
      $object = $assembler->find($langCounter)->first();
      $totalRows = \intval($object->get('COUNT(TableId)'));
      $rpp = \intval($this->request->getProperty('rpp'));
      $totalPages = (int) \ceil($totalRows / $rpp);
      $page = \intval($this->request->getProperty('page'));

      $langIdentity = $langFactory->getIdentity();
      $langIdentity->field("TableId")->eq($tableid)->orderByAsc("TextId")->orderByAsc("SeqId")->limit(($page - 1) * $rpp, $rpp);
      $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TypeId' => 'i'), "EsoTable");
      $langIdentity->join($esoTableIdentity, "TableId", "TableId");
      $collection = $assembler->find($langIdentity);
      return $collection->getGenerator();
   }
   
   /**
    * Reads contents of a metatable.
    * @param type $tableid Metatable ID.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data. 
    */
   private function readMeta($tableid, &$page, &$totalPages)
   {
      $metaTable = $this->readTables($tableid);
      
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($langFactory);

      // Count rows.
      $langCounter = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "Lang");
      $langCounter->count("TableId");
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $langCounter->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $langCounter->ior();
         }
      }
      $object = $assembler->find($langCounter)->first();
      $totalRows = \intval($object->get('COUNT(TableId)'));
      $rpp = \intval($this->request->getProperty('rpp'));
      $totalPages = (int) \ceil($totalRows / $rpp);
      $page = \intval($this->request->getProperty('page'));

      // Select strings.
      $langIdentity = $langFactory->getIdentity();
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $langIdentity->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $langIdentity->ior();
         }
      }
      $langIdentity->orderByAsc("TextId")->orderByAsc("SeqId")->orderByFieldAsc("TableId", \array_values($metaTable))->limit(($page - 1) * $rpp, $rpp);
      $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TypeId' => 'i'), "EsoTable");
      $langIdentity->join($esoTableIdentity, "TableId", "TableId");
      $collection = $assembler->find($langIdentity);
      return $collection->getGenerator();
   }

}
