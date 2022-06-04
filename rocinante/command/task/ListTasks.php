<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/command/task/ListTaskUtil.php';

/**
 * ListTasks creates an HTML table that shows every task.
 */
class ListTasks extends \rocinante\controller\Command
{

   use \rocinante\command\task\ListTaskUtil
   {
      getTypeCaption as private;
      getTableName as private;
   }

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * Creates an HTML table that shows every task.
    */
   public function doExecute()
   {
      // Get the current user.
      $session = \rocinante\command\SessionRegistry::instance();
      $session->resume();

      if ($this->request->getProperty('cmd') === "task/ListTasks" && $session->getType() === "ADMIN")
      {
         // Sorting column index.
         $columns = array("TaskId", "Date", "Type", "Description", "Size", "Progress", "Translator.Username", "Assigner.Username");
         $index = \intval($this->request->getProperty('column'));
         $column = $index === -1 ? null : $columns[$index];

         // Set localization.
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $decimalMark = (string) $this->l10n->format->{"decimal-mark"};
         $thousandsMark = (string) $this->l10n->format->{"thousands-mark"};
         $tableL10n = $this->l10n->{"frontpage"}->{"tabs"}->{"tasks"}->{"table"};

         // Build the query.
         $taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
         $assembler = new \rocinante\persistence\DomainAssembler($taskFactory);

         // Count tasks.
         $taskCounter = new \rocinante\mapper\identity\Identity(array('TaskId' => 'i'), "Task");
         $taskCounter->count("TaskId");
         $object = $assembler->find($taskCounter)->first();
         $totalRows = \intval($object->get('COUNT(TaskId)'));
         $rpp = \intval($this->request->getProperty('rpp'));
         $totalPages = (int) \ceil($totalRows / $rpp);
         $page = \intval($this->request->getProperty('page'));

         $taskIdentity = $taskFactory->getIdentity();
         $esoTableIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'Number' => 'i', 'Description' => 's'), "EsoTable");
         if ($column === "Description")
         {
            if ($this->request->getProperty('asc') === "true")
            {
               $esoTableIdentity->orderByAsc($column);
            }
            else
            {
               $esoTableIdentity->orderByDesc($column);
            }
         }

         $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
         $userIdentity->alias("Translator");
         if ($column === "Translator.Username")
         {
            if ($this->request->getProperty('asc') === "true")
            {
               $userIdentity->orderByAsc("Username");
            }
            else
            {
               $userIdentity->orderByDesc("Username");
            }
         }

         $assignerIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
         $assignerIdentity->alias("Assigner");
         if ($column === "Assigner.Username")
         {
            if ($this->request->getProperty('asc') === "true")
            {
               $assignerIdentity->orderByAsc("Username");
            }
            else
            {
               $assignerIdentity->orderByDesc("Username");
            }
         }

         $taskIdentity->distinct()->leftJoin($esoTableIdentity, "Task.TableId", "EsoTable.TableId")->join($userIdentity, "Task.UserId", "Translator.UserId")->join($assignerIdentity, "Task.AssignerId", "Assigner.UserId");
         if ($column === "TaskId" || $column === "Date" || $column === "Type" || $column === "Size" || $column == "Progress")
         {
            if ($this->request->getProperty('asc') === "true")
            {
               $taskIdentity->orderByAsc($column);
            }
            else
            {
               $taskIdentity->orderByDesc($column);
            }
         }
         $taskIdentity->limit(($page - 1) * $rpp, $rpp);
         $collection = $assembler->find($taskIdentity);
         $generator = $collection->getGenerator();

         // Build the table.
         $html  = "<thead>\n";
         $html .= "<tr>\n";
         $html .= "<th style='width: 5%'>" . $tableL10n->id . "</th>\n";
         $html .= "<th style='width: 5%'>" . $tableL10n->date . "</th>\n";
         $html .= "<th style='width: 10%'>" . $tableL10n->type . "</th>\n";
         $html .= "<th style='width: 33%'>" . $tableL10n->description . "</th>\n";
         $html .= "<th style='width: 9%'>" . $tableL10n->count . "</th>\n";
         $html .= "<th style='width: 13%'>" . $tableL10n->progress . "</th>\n";
         $html .= "<th style='width: 12%'>" . $tableL10n->assigned . "</th>\n";
         $html .= "<th style='width: 13%'>" . $tableL10n->assigner . "</th>\n";
         $html .= "</tr>\n";
         $html .= "</thead>\n";
         $html .= "<tbody>\n";
         $rowCounter = 0;
         foreach ($generator as $object)
         {
            $html .= "<tr>\n";
            $html .= "<td>" . \number_format($object->get('Task.TaskId'), 0, $decimalMark, $thousandsMark) . "</td>\n";
            $date = \DateTime::createFromFormat('Y-n-j', $object->get('Task.Date'));
            $html .= "<td>" . $date->format($this->l10n->{"format"}->{"date-format"}) . "</td>\n";
            $html .= "<td>" . $this->getTypeCaption($object->get('Task.Type')) . "</td>\n";
            $html .= "<td>" . $object->get('EsoTable.Description') . " " . $this->getTableName($object->get('EsoTable.TableId'), $object->get('EsoTable.Number'), $object->get('Task.Term')) . "</td>\n";
            $html .= "<td>" . \number_format($object->get('Task.Size'), 0, $decimalMark, $thousandsMark) . "</td>\n";
            $progress = \number_format($object->get('Task.Progress'), 2, $decimalMark, $thousandsMark) . "%";
            $html .= "<td>$progress</td>\n";
            $html .= "<td>" . $object->get('Translator.Username') . "</td>\n";
            $html .= "<td>" . $object->get('Assigner.Username') . "</td>\n";
            $html .= "</tr>\n";
            $rowCounter++;
         }
         if ($rowCounter === 0)
         {
            $html  = "<tbody>\n";
            $html .= "<tr>\n";
            $html .= "<td class='ui-widget-content' style='width: 100%; text-align: center'>" . $this->l10n->{"frontpage"}->{"tabs"}->{"tasks"}->{"tasks-empty"} . "</td>\n";
            $html .= "</tr>\n";
         }
         $html .= "</tbody>\n";

         $response = array("count" => $rowCounter, "page" => $page, "total" => $totalPages, "html" => $html);
         echo \json_encode($response);
      }
      else
      {
         echo \json_encode(array("count" => -1));
      }
   }

}
