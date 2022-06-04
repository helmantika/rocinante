<?php

namespace rocinante\command;

require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * GetLocalization returns a JSON string with configuration parameters, that is, those ones that 
 * were set during installation process.
 */
class GetLocalization extends \rocinante\controller\Command
{

   /**
    * Extracts configuration parameters from a language file.
    * @return string A JSON string.
    */
   function doExecute()
   {
      if ($this->request->getProperty('cmd') === "GetLocalization")
      {
         // Get every table type description. 
         $types = array();
         $langTypeFactory = new \rocinante\persistence\PersistenceFactory("LangType");
         $assemblerLangTypeFactory = new \rocinante\persistence\DomainAssembler($langTypeFactory);
         $langTypeIdentity = $langTypeFactory->getIdentity();
         $langTypeIdentity->orderByAsc('TypeId');
         $langTypeCollection = $assemblerLangTypeFactory->find($langTypeIdentity);
         $generator = $langTypeCollection->getGenerator();
         foreach ($generator as $langType)
         {
            $types[\intval($langType->get('TypeId'))] = array("tooltip" => $langType->get('Description'),
                                                              "color" => $langType->get('Color'));
         }
         
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $theme = $session->getTheme();
         $array = array("format" => array("thousands" => (string) $l10n->{"format"}->{"thousands-mark"},
                                          "decimal" => (string) $l10n->{"format"}->{"decimal-mark"},
                                          "dateTime" => $this->toMoment((string) $l10n->{"format"}->{"datetime-format"}),
                                          "date" => $this->toMoment((string) $l10n->{"format"}->{"date-format"}),
                                          "time" => $this->toMoment((string) $l10n->{"format"}->{"time-format"})),
                        "pager" => array("page" => (string) $l10n->{"pager"}-> {"page"},
                                         "of" => (string) $l10n->{"pager"}-> {"of"}),
                        "dialog" => array("ok" => (string) $l10n->{"dialog"}->{"ok-button"},
                                          "cancel" => (string) $l10n->{"dialog"}->{"cancel-button"},
                                          "yes" => (string) $l10n->{"dialog"}->{"yes"},
                                          "no" => (string) $l10n->{"dialog"}->{"no"},
                                          "send" => (string) $l10n->{"dialog"}->{"send-button"},
                                          "save" => (string) $l10n->{"dialog"}->{"save-button"}),
                        "status" => array("editing" => (string) $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"string-status"}->{"editing"},
                                          "disputed" => (string) $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"string-status"}->{"disputed"},
                                          "locked" => (string) $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"string-status"}->{"locked"},
                                          "revised" => (string) $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"string-status"}->{"revised"},
                                          "notRevised" => (string) $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"string-status"}->{"not-revised"},
                                          "notTranslated" => (string) $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"string-status"}->{"not-translated"}),
                        "taskType" => array((string) $l10n->{"task-type"}->{"translation"} => "TRANSLATION",
                                            (string) $l10n->{"task-type"}->{"revision"} => "REVISION",
                                            (string) $l10n->{"task-type"}->{"updating"} => "UPDATING",
                                            (string) $l10n->{"task-type"}->{"glossary"} => "GLOSSARY"),
                        "tips" => \rocinante\view\ViewHelper::instance()->getTips(), 
                        "misc" => array("task" => (string) $l10n->{"frontpage"}->{"tabs"}->{"tasks"}->{"task-number"},
                                        "code" => (string) $l10n->{"frontpage"}->{"code-caption"},
                                        "searchLang" => (string) $l10n->{"frontpage"}->{"search-caption"},
                                        "searchLua" => (string) $l10n->{"frontpage"}->{"search-caption"} . " (" . (string) $l10n->{"frontpage"}->{"tabs"}->{"master-table"}->{"lua-table-id"} . ")",
                                        "theme" => (string) $theme),
                        "types" => $types);

         echo \json_encode($array);
      }
   }

   /**
    * Converts a PHP date/time format into a Moment JS one.
    * @param string $format A date/time in PHP format.
    * @return string A date/time format in Moment JS format.
    */
   private function toMoment($format)
   {
      return \str_replace(['j', 'n', 'Y', 'G', 'i', 's'], ['D', 'M', 'YYYY', 'HH', 'mm', 'ss'], $format);
   }

}
