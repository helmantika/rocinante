<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * ListStats creates an HTML table that shows user statistics.
 */
class ListStats extends \rocinante\controller\Command
{

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * Creates an HTML table that shows user statistics.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/ListStats")
      {
         // Sorting column index.
         $columns = array("Username", "Translated", "Revised", /*"Updated",*/ "Since", "Last");
         $index = \intval($this->request->getProperty('column'));
         $column = $index === -1 ? null : $columns[$index];
         
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $decimalMark = (string) $this->l10n->format->{"decimal-mark"};
         $thousandsMark = (string) $this->l10n->format->{"thousands-mark"};
         
         $tablel10n = $this->l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"stats"}->{"table"};
         $factory = new \rocinante\persistence\PersistenceFactory("Stats");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $statsCounter = new \rocinante\mapper\identity\Identity(array('UserId' => 'i'), "User");
         $statsCounter->count("UserId");
         $object = $assembler->find($statsCounter)->first();
         $totalRows = \intval($object->get('COUNT(UserId)'));
         $rpp = \intval($this->request->getProperty('rpp'));
         $totalPages = (int) \ceil($totalRows / $rpp);
         $page = \intval($this->request->getProperty('page'));
         
         $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's', 'Since' => 's'), "User");
         if ($column === "Username" || $column === "Since")
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
         else if ($column === null)
         {
            $userIdentity->orderByAsc("Username");
         }
         
         $statsIdentity = $factory->getIdentity();
         if ($column === "Translated" || $column === "Revised"/* || $column === "Updated"*/ || $column === "Last")
         {
            if ($this->request->getProperty('asc') === "true")
            {
               $statsIdentity->orderByAsc($column);
            }
            else
            {
               $statsIdentity->orderByDesc($column);
            }
         }
         $statsIdentity->join($userIdentity, "UserId", "UserId");
         $statsIdentity->limit(($page - 1) * $rpp, $rpp);
         $collection = $assembler->find($statsIdentity);
         
         $html  = "<thead>\n";
         $html .= "<tr>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"username"} . "</th>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"translated"} . "</th>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"revised"} . "</th>\n";
         //$html .= "<th style='width: 15%'>" . $tablel10n->{"updated"} . "</th>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"since"} . "</th>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"last-action"} . "</th>\n";
         $html .= "</tr>\n";
         $html .= "</thead>\n";
         $html .= "<tbody>\n";
            
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $html .= "<tr>\n";
            $html .= "<td>" . $object->get('User.Username') . "</td>\n";
            $html .= "<td>" . \number_format($object->get('Stats.Translated'), 0, $decimalMark, $thousandsMark) . "</td>\n";
            $html .= "<td>" . \number_format($object->get('Stats.Revised'), 0, $decimalMark, $thousandsMark) . "</td>\n";
            //$html .= "<td>" . \number_format($object->get('Stats.Updated'), 0, $decimalMark, $thousandsMark) . "</td>\n";
            $date1 = \DateTime::createFromFormat('Y-n-j', $object->get('User.Since'));
            $html .= "<td>" . $date1->format($this->l10n->{"format"}->{"date-format"}) . "</td>\n";
            $date2 = \DateTime::createFromFormat('Y-n-j', $object->get('Stats.Last'));
            $html .= "<td>" . $date2->format($this->l10n->{"format"}->{"date-format"}) . "</td>\n";
            $html .= "</tr>\n";
         }
         
         $html .= "</tbody>\n";
         
         $response = array("page" => $page, "total" => $totalPages, "html" => $html);
         echo \json_encode($response);
      }
   }
   
}


            