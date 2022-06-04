<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/translation/MetaTable.php';

/**
 * ListBriefEsoTables creates an HTML table that shows number and description of ESO tables.
 */
class ListBriefEsoTables extends \rocinante\controller\Command
{

   use \rocinante\command\translation\MetaTable
   {
      readEveryTable as protected;
   }
   
   /**
    * Creates an HTML table that shows number and description of ESO tables.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/ListBriefEsoTables")
      {
         $factory = new \rocinante\persistence\PersistenceFactory("EsoTable");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $tableL10n = $l10n->frontpage->tabs->{"master-table"}->table;

         $html = "<thead>\n";
         $html .= "<tr>\n";
         $html .= "<th>TableId</th>\n"; // Hidden column.
         $html .= "<th style='width: 10%'>" . $tableL10n->id . "</th>\n";
         $html .= "<th>" . $tableL10n->description . "</th>\n";
         $html .= "</tr>\n";
         $html .= "</thead>\n";
         $html .= "<tbody>\n";
         
         $tables = $this->readEveryTable();
         $identity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'Number' => 'i', 'Description' => 'i'), "EsoTable");
         $identity->orderByAsc("Number");
         $collection = $assembler->find($identity);
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $tableid = \intval($object->get('TableId'));
            // If table is not part of a metatable, list it.
            if (\array_search($tableid, $tables) === false)
            {
               $html .= "<tr>\n";
               $html .= "<td>$tableid</td>\n";
               if ($tableid === 0)
               {
                  $number = $l10n->frontpage->tabs->{"master-table"}->{"lua-table-id"};
               } 
               else if ($tableid < 0xff)
               {
                  $number = "meta" . \str_pad($object->get('TableId'), 3, '0', \STR_PAD_LEFT);
               } 
               else
               {
                  $number = "lang" . \str_pad($object->get('Number'), 3, '0', \STR_PAD_LEFT);
               }
               $html .= "<td>$number</td>\n";
               $html .= "<td>" . $object->get('Description') . "</td>\n";
               $html .= "<tr>";
            }
         }

         $html .= "</tbody>\n";

         $response = array("html" => $html);
         echo \json_encode($response);
      }
   }

}
