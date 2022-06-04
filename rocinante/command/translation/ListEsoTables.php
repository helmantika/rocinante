<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/mapper/identity/UserIdentity.php';

/**
 * ListEsoTables creates an HTML table that shows status of ESO tables.
 */
class ListEsoTables extends \rocinante\controller\Command
{

   const COLUMN_NUMBER = 0;
   const COLUMN_DESCRIPTION = 1;
   const COLUMN_SIZE = 2;
   const COLUMN_TRANSLATED = 3;
   const COLUMN_REVISED = 4;
   //const COLUMN_NEW = 5;
   //const COLUMN_MODIFIED = 6;
   const COLUMN_TRANSLATORS = 5;// 7;
   const COLUMN_REVISORS = 6;// 8;
   const COLUMN_TABLEID = 7;// 9;

   /**
    * The EsoTable persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $esoTableFactory = null;
   
   /**
    * The Worker persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $workerFactory = null;
   
   /**
    * The domain object assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $assembler = null;
   
   /**
    * The XML localization file root.
    * @var \SimpleXMLElement 
    */
   private $l10n = null;

   /**
    * The decimal separator for real numbers.
    * @var string
    */
   private $decimalMark;
   
   /**
    * The thousands separator for numbers.
    * @var string
    */
   private $thousandsMark;
   
   /**
    * Creates an HTML table that shows the ESO table index.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/ListEsoTables")
      {
         $this->esoTableFactory = new \rocinante\persistence\PersistenceFactory("EsoTable");
         $this->workerFactory = new \rocinante\persistence\PersistenceFactory("Worker");
         $this->assembler = new \rocinante\persistence\DomainAssembler($this->esoTableFactory);
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $this->decimalMark = (string) $this->l10n->format->{"decimal-mark"};
         $this->thousandsMark = (string) $this->l10n->format->{"thousands-mark"};
         $tableL10n = $this->l10n->frontpage->tabs->{"master-table"}->table;

         $html  = "<thead>\n";
         $html .= "<tr>\n";
         $html .= "<th>TableId</th>\n"; // Hidden column.
         $html .= "<th style='width: 8%'>" . $tableL10n->id . "</th>\n";
         $html .= "<th style='width: 38%'>" . $tableL10n->description . "</th>\n";
         $html .= "<th style='width: 8%'>" . $tableL10n->strings . "</th>\n";
         $html .= "<th style='width: 8%'>" . $tableL10n->translated . "</th>\n";
         $html .= "<th style='width: 8%'>" . $tableL10n->revised . "</th>\n";
         //$html .= "<th>" . $tableL10n->new . "</th>\n";
         //$html .= "<th>" . $tableL10n->modified . "</th>\n";
         $html .= "<th style='width: 15%'>" . $tableL10n->translating . "</th>\n";
         $html .= "<th style='width: 15%'>" . $tableL10n->revising . "</th>\n";
         $html .= "</tr>\n";
         $html .= "</thead>\n";
         $html .= "<tbody>\n";
         $html .= $this->listLuaTables();
         $html .= $this->listMetaTables();
         $html .= $this->listLangTables();
         $html .= "</tbody>\n";

         echo $html;
      }
   }

   /**
    * Creates an HTML table that lists tables from The Elder Scrolls Online LUA files (pregame and
    * client).
    * @return string HTML code.
    */
   private function listLuaTables()
   {
      $html = null;
      $result = array();
      
      $esoTableIdentity = $this->esoTableFactory->getIdentity();
      $esoTableIdentity->field("TableId")->eq(0);
      $workerIdentity = $this->workerFactory->getIdentity();
      $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
      $esoTableIdentity->leftJoin($workerIdentity, "TableId", "TableId")->leftJoin($userIdentity, "UserId", "UserId");
      $collection = $this->assembler->find($esoTableIdentity);
      $this->buildRows($collection, $result);
      foreach ($result as $row)
      {
         $html .= "<tr>\n";
         $html .= "<td>" . $row[self::COLUMN_TABLEID] . "</td>\n";
         $html .= "<td>" . $this->l10n->frontpage->tabs->{"master-table"}->{"lua-table-id"} . "</td>\n";
         $this->createRow($html, $row);
      }

      return $html;
   }

   /**
    * Creates an HTML table that lists existing metatables. A metatable is a table that includes
    * strings from different tables extracted from The Elder Scrolls Online lang file in such a way
    * that the metatable has a logical sequence of content, i.e. dialogs.
    * @return string HTML code.
    */
   private function listMetaTables()
   {
      $html = null;
      $result = array();
      
      $esoTableIdentity = $this->esoTableFactory->getIdentity();
      $esoTableIdentity->field("TableId")->gt(0)->iand()->field("TableId")->lt(0xff)->orderByAsc("TableId");
      $workerIdentity = $this->workerFactory->getIdentity();
      $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
      $esoTableIdentity->leftJoin($workerIdentity, "TableId", "TableId")->leftJoin($userIdentity, "UserId", "UserId");
      $collection = $this->assembler->find($esoTableIdentity);
      $this->buildRows($collection, $result);
      foreach ($result as $row)
      {
         $html .= "<tr>\n";
         $html .= "<td>" . $row[self::COLUMN_TABLEID] . "</td>\n";
         $html .= "<td>meta" . \str_pad($row[self::COLUMN_TABLEID], 3, '0', \STR_PAD_LEFT) . "</td>\n";
         $this->createRow($html, $row);
      }

      return $html;
   }
   
   /**
    * Creates an HTML table that lists tables from The Elder Scrolls Online lang file excluding
    * tables that are part of a metatable.
    * @return string HTML code.
    */
   private function listLangTables()
   {
      $html = null;
      $result = array();
      
      $esoTableIdentity = $this->esoTableFactory->getIdentity();
      $esoTableIdentity->field("TableId")->ge(0xff)->iand()->field("TableId")->nin("(SELECT DISTINCT TableId FROM MetaTable)")->orderByAsc("Number");
      $workerIdentity = $this->workerFactory->getIdentity();
      $userIdentity = new \rocinante\mapper\identity\Identity(array('UserId' => 'i', 'Username' => 's'), "User");
      $esoTableIdentity->leftJoin($workerIdentity, "TableId", "TableId")->leftJoin($userIdentity, "UserId", "UserId");
      $collection = $this->assembler->find($esoTableIdentity);
      $this->buildRows($collection, $result);
      foreach ($result as $row)
      {
         $html .= "<tr>\n";
         $html .= "<td>" . $row[self::COLUMN_TABLEID] . "</td>\n";
         $html .= "<td>lang" . \str_pad($row[self::COLUMN_NUMBER], 3, '0', \STR_PAD_LEFT) . "</td>\n";
         $this->createRow($html, $row);
      }

      return $html;
   }

   /**
    * 
    * @param type $collection
    * @param type $result
    */
   private function buildRows(&$collection, &$result)
   {
      $lastRow = null;
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         if ($lastRow === null || $object->get('EsoTable.TableId') !== $lastRow[self::COLUMN_TABLEID])
         {
            $row = array($object->get('EsoTable.Number'),
                         $object->get('EsoTable.Description'),
                         $object->get('EsoTable.Size'),
                         $object->get('EsoTable.Translated'),
                         $object->get('EsoTable.Revised'),
                         //$object->get('EsoTable.New'),
                         //$object->get('EsoTable.Modified'),
                         $object->get('Worker.IsTranslating') ? $object->get('User.Username') : null,
                         $object->get('Worker.IsRevising') ? $object->get('User.Username') : null,
                         $object->get('EsoTable.TableId'));
            $result[] = $row;
            $lastRow = $row;
         }
         else
         {
            if ($object->get('Worker.IsTranslating') )
            {
               $lastRow[self::COLUMN_TRANSLATORS] .= ($lastRow[self::COLUMN_TRANSLATORS] !== null ? ", " : "") . $object->get('User.Username');
            }
            if ($object->get('Worker.IsRevising') )
            {
               $lastRow[self::COLUMN_REVISORS] .= ($lastRow[self::COLUMN_REVISORS] !== null ? ", " : "") . $object->get('User.Username');
            }
            $result[\count($result) - 1] = $lastRow;
         }
      }
   }

   /**
    * Creates HTML code for a row of the master table.
    * @param string $html New HTML code will be append to this one.
    * @param \rocinante\domain\MasterTable $object A master table object with row data.
    */
   private function createRow(&$html, &$row)
   {
      $html .= "<td>" . $row[self::COLUMN_DESCRIPTION] . "</td>\n";
      $html .= "<td>" . \number_format($row[self::COLUMN_SIZE], 0, $this->decimalMark, $this->thousandsMark) . "</td>\n";
      $translated = \number_format($row[self::COLUMN_TRANSLATED], 2, $this->decimalMark, $this->thousandsMark);
      $html .= "<td>$translated%</td>\n";
      $revised = \number_format($row[self::COLUMN_REVISED], 2, $this->decimalMark, $this->thousandsMark);
      $html .= "<td>$revised%</td>\n";
      //$html .= "<td>" . $row[self::COLUMN_NEW] . "</td>\n";
      //$html .= "<td>" . $row[self::COLUMN_MODIFIED] . "</td>\n";
      $html .= "<td>" . $row[self::COLUMN_TRANSLATORS] . "</td>\n";
      $html .= "<td>" . $row[self::COLUMN_REVISORS] . "</td>\n";
      $html .= "</tr>\n";
   }
}
