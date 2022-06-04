<?php

namespace rocinante\command\task;

require_once 'rocinante/view/ViewHelper.php';

/**
 * ListTaskUtil is a trait that offers methods that help to list tasks.
 */
trait ListTaskUtil
{

   /**
    * Composes a localized string that contains the type of task.
    * @param string $type TRANSLATION, REVISION, UPDATING, or GLOSSARY.
    * @return string A localized string.
    */
   public function getTypeCaption($type)
   {
      $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
      $label = $l10n->{"task-type"};
      $captions = array('TRANSLATION' => $label->{"translation"},
                        'REVISION' => $label->{"revision"},
                        'UPDATING' => $label->{"updating"},
                        'GLOSSARY' => $label->{"glossary"});
      return $captions[$type];
   }

   /**
    * Builds a table name from a given table ID.
    * @param int $tableid A table ID.
    * @param int $number A table number.
    * @param string $term A term to change in a glossary task.
    * @return string A table name.
    */
   public function getTableName($tableid, $number, $term)
   {
      $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
      if ($tableid === null)
      {
         if ($term === null)
         {
            $name = $l10n->frontpage->tabs->{"master-table"}->{"misc-table-id"};
         }
         else
         {
            $name = \sprintf($l10n->frontpage->tabs->{"master-table"}->{"misc-term-id"}, str_replace("\'", "'", $term));
         }
      }
      else if ($tableid === 0)
      {
         $name = "(" . $l10n->frontpage->tabs->{"master-table"}->{"lua-table-id"} . ")";
      }
      else if ($tableid < 0xff)
      {
         $name = "(meta" . \str_pad($tableid, 3, '0', \STR_PAD_LEFT) . ")";
      }
      else
      {
         $name = "(lang" . \str_pad($number, 3, '0', \STR_PAD_LEFT) . ")";
      }
      return $name;
   }

}
