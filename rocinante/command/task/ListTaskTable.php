<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/translation/MetaTable.php';

/**
 * ListTaskTable creates an HTML table that shows strings to translate for a given task.
 */
class ListTaskTable extends \rocinante\controller\Command
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
    * The Task persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $taskFactory = null;
   
   /**
    * The Task object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $taskAssembler = null;
   
   /**
    * The task that contains the required data.
    * @var \rocinante\domain\model\Task
    */
   private $task = null;
   
   /**
    * Creates an HTML table that shows strings to translate for a given task.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/ListTaskTable")
      {
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $taskid = \intval($this->request->getProperty('taskid'));
         $page = $totalPages = 0;        

         $this->taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
         $this->taskAssembler = new \rocinante\persistence\DomainAssembler($this->taskFactory);
         $identity = new \rocinante\mapper\identity\Identity(array('TaskId' => 'i', 'TableId' => 'i', 'Type' => 's', 'Size' => 'i'), "Task");
         $identity->field("TaskId")->eq($taskid);
         $this->task = $this->taskAssembler->find($identity)->first();
         $tableid = $this->task->get('TableId') !== null ? \intval($this->task->get('TableId')) : PHP_INT_MAX;
         $typeOfTask = $this->task->get('Type');
      
         if ($tableid == 0)
         {
            $generator = $this->readLua($taskid, $page, $totalPages);
            $prefix = "Lua";
         }
         else if ($tableid < 0xff)
         {
            $generator = $this->readMeta($tableid, $taskid, $page, $totalPages);
            $prefix = "Lang";            
         }
         else
         {
            $generator = $this->readLang($taskid, $page, $totalPages);
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
            $html .= "<td rowspan=3>" . $object->get("TaskContents.Done") . "</td>\n";
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
         
         
         $response = array("page" => $page, "total" => $totalPages, "html" => $html, "typeOfTask" => $typeOfTask);
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
    * Reads contents of a task that is bind to Lua table.
    * @param int $taskid A task ID.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data. 
    */
   private function readLua($taskid, &$page, &$totalPages)
   {
      $totalRows = \intval($this->task->get('Size'));
      $rpp = \intval($this->request->getProperty('rpp'));
      $totalPages = (int) \ceil($totalRows / $rpp);
      $page = \intval($this->request->getProperty('page'));
      
      $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($luaFactory);

      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsIdentity = $taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TaskId")->eq(\intval($taskid))->orderByAsc("LuaTextId");

      $luaIdentity = $luaFactory->getIdentity();
      $luaIdentity->limit(($page - 1) * $rpp, $rpp);
      $luaIdentity->join($taskContentsIdentity, "Lua.TextId", "TaskContents.LuaTextId");
      
      $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TypeId' => 'i'), "EsoTable");
      $luaIdentity->join($esoTableIdentity, "Lua.TableId", "EsoTable.TableId");
      
      $collection = $assembler->find($luaIdentity);
      return $collection->getGenerator();
   }
   
   /**
    * Reads contents of a task that is bind to a metatable.
    * @param int $tableid A metatable ID.
    * @param int $taskid A task ID.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data. 
    */
   private function readMeta($tableid, $taskid, &$page, &$totalPages)
   {
      $totalRows = \intval($this->task->get('Size'));
      $rpp = \intval($this->request->getProperty('rpp'));
      $totalPages = (int) \ceil($totalRows / $rpp);
      $page = \intval($this->request->getProperty('page'));
      
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($langFactory);
      
      $metaTable = $this->readTables($tableid);
      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsIdentity = $taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TaskId")->eq(\intval($taskid))->orderByAsc("TextId")->orderByAsc("SeqId")->orderByFieldAsc("TableId", \array_values($metaTable));
      
      $fields = array("TableId", "TextId", "SeqId");
      $langIdentity = $langFactory->getIdentity();
      $langIdentity->limit(($page - 1) * $rpp, $rpp);
      $langIdentity->join($taskContentsIdentity, $fields, $fields);
      $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TypeId' => 'i'), "EsoTable");
      $langIdentity->join($esoTableIdentity, "Lang.TableId", "EsoTable.TableId");
      
      $collection = $assembler->find($langIdentity);
      return $collection->getGenerator();
   }
   
   /**
    * Reads contents of a task that is bind to a Lang table.
    * @param int $taskid A task ID.
    * @param int $page Returns the current page.
    * @param int $totalPages Returns the total number of pages.
    * @return object A generator to iterate over data. 
    */
   private function readLang($taskid, &$page, &$totalPages)
   {
      $totalRows = \intval($this->task->get('Size'));
      $rpp = \intval($this->request->getProperty('rpp'));
      $totalPages = (int) \ceil($totalRows / $rpp);
      $page = \intval($this->request->getProperty('page'));
      
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($langFactory);
      
      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsIdentity = $taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TaskId")->eq(\intval($taskid))->orderByAsc("TextId")->orderByAsc("SeqId");
      
      $fields = array("TableId", "TextId", "SeqId");
      $langIdentity = $langFactory->getIdentity();
      $langIdentity->limit(($page - 1) * $rpp, $rpp);
      $langIdentity->join($taskContentsIdentity, $fields, $fields);
      $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TypeId' => 'i'), "EsoTable");
      $langIdentity->join($esoTableIdentity, "Lang.TableId", "EsoTable.TableId");
      
      $collection = $assembler->find($langIdentity);
      return $collection->getGenerator();
   }
   
}
