<?php

namespace rocinante\command\task;

require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/task/ListTaskUtil.php';

/**
 * ListUserTasks creates an HTML table that shows tasks assigned by a given user.
 */
class ListAssignerTasks extends \rocinante\controller\Command
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
    * Creates an HTML table that shows tasks assigned by a given user.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/ListAssignerTasks")
      {
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         
         // If the user is a translator then nothing to do.
         if ($session->getType() !== "TRANSLATOR")
         {
            // Sorting column index.
            $columns = array("TaskId", "Date", "Type", "Description", "Size", "Progress", "Username");
            $index = \intval($this->request->getProperty('column'));
            $column = $index === -1 ? null : $columns[$index];
         
            // Get current user.
            $assignerid = $session->getUserId();

            // Set localization.
            $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
            $decimalMark = (string) $this->l10n->format->{"decimal-mark"};
            $thousandsMark = (string) $this->l10n->format->{"thousands-mark"};
            $tableL10n = $this->l10n->{"frontpage"}->{"tabs"}->{"tasks"}->{"table"};

            // Build the query.
            $taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
            $assembler = new \rocinante\persistence\DomainAssembler($taskFactory);

            // Count tasks.
            $taskCounter = $taskFactory->getIdentity();
            $taskCounter->distinct()->count("TaskId")->field("AssignerId")->eq($assignerid)->iand()->field('UserId')->neq($assignerid);
            $object = $assembler->find($taskCounter)->first();
            $totalRows = \intval($object->get('COUNT(TaskId)'));
            $rpp = \intval($this->request->getProperty('rpp'));
            $totalPages = (int) \ceil($totalRows / $rpp);
            $page = \intval($this->request->getProperty('page'));

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
            if ($column === "Username")
            {
               if ($this->request->getProperty('asc') === "true")
               {
                  $userIdentity->orderByAsc($column);
               }
               else
               {
                  $userIdentity->orderByDesc($column);
               }
            }
          
            $taskIdentity = $taskFactory->getIdentity();
            $taskIdentity->distinct()->field("AssignerId")->eq($assignerid)->iand()->field('UserId')->neq($assignerid)->leftJoin($esoTableIdentity, "TableId", "TableId")->join($userIdentity, "Task.UserId", "User.UserId");
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
            $html .= "<th>" . $tableL10n->id . "</th>\n";
            $html .= "<th>" . $tableL10n->date . "</th>\n";
            $html .= "<th>" . $tableL10n->type . "</th>\n";
            $html .= "<th style='width: 50%'>" . $tableL10n->description . "</th>\n";
            $html .= "<th>" . $tableL10n->count . "</th>\n";
            $html .= "<th>" . $tableL10n->progress . "</th>\n";
            $html .= "<th style='width: 12%'>" . $tableL10n->assigned . "</th>\n";
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
               $html .= "<td>" . $object->get('User.Username') . "</td>\n";
               $html .= "</tr>\n";
               $rowCounter++;
            }
            if ($rowCounter === 0)
            {
               $html  = "<tbody>\n";
               $html .= "<tr>\n";
               $html .= "<td class='ui-widget-content' style='width: 100%; text-align: center'>" . $this->l10n->{"frontpage"}->{"tabs"}->{"tasks"}->{"no-assigned-tasks"} . "</td>\n";
               $html .= "</tr>\n";
            }
            $html .= "</tbody>\n";

            $response = array("count" => $rowCounter, "page" => $page, "total" => $totalPages, "html" => $html);
         }
         else 
         {
            // Invalid type of user.
            $response = array("count" => -1, "html" => null);
         }
         echo \json_encode($response);
      }
   }
   
}
