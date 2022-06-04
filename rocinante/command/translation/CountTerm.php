<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * CountTerm counts how many times a given term appears in English records.
 */
class CountTerm extends \rocinante\controller\Command
{
   
   /**
    * Counts how many times a given term appears in English records.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/CountTerm")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $count = 0;
         $row = null;
         $message = (string) $l10n->{"dialog"}->{"glossary"}->{"cannot-count-term"};
         
         // Resume the current session.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         
         // Validate request field.
         $sqlm = \rocinante\persistence\SqlManager::instance();
         $term = $sqlm->escape($this->request->getProperty('term'));
         if (isset($term) && \strlen($term) > 2)
         {
            $statement = "SELECT COUNT(*) FROM Lang WHERE En REGEXP CONCAT('[[:<:]]', '$term', '[[:>:]]')";
            $sqlm->query($statement);
            $row = $sqlm->fetchAssoc($statement);
            $sqlm->close($statement);
            if ($row !== null)
            {
               $count = \intval($row['COUNT(*)']);
               
               
               if ($count === 0)
               {
                  $message = \sprintf((string) $l10n->{"dialog"}->{"glossary"}->{"counter-no-matches"}, $this->request->getProperty('term'));
               } 
               else if ($count === 1)
               {
                  $message = \sprintf((string) $l10n->{"dialog"}->{"glossary"}->{"counter-one-match"}, $this->request->getProperty('term'));
               } 
               else
               {
                  $decimalMark = (string) $l10n->format->{"decimal-mark"};
                  $thousandsMark = (string) $l10n->format->{"thousands-mark"};
                  $message = \sprintf((string) $l10n->{"dialog"}->{"glossary"}->{"counter-many-matches"}, $this->request->getProperty('term'), \number_format($count, 0, $decimalMark, $thousandsMark));
               }
            }
         }
      }
      
      $array["html"] = $message;
      echo \json_encode($array);
   }
}
