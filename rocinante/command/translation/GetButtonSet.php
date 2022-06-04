<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * GetButtonSet returns the translation button set that depends on type of user.
 */
class GetButtonSet extends \rocinante\controller\Command
{

   /**
    * Generates HTML buttons that depend on type of user.
    * @return string A JSON string.
    */
   function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/GetButtonSet")
      {
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $usertype = $session->getType();
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $buttonset = $l10n->{"frontpage"}->{"tabs"}->{"work"}->{"buttonset"};
         $html  = "<tr><td class='ui-widget-header'></td><td class='ui-widget-header'>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-save' title='" . $buttonset->{"save"} . "'>" . $buttonset->{"save"}['caption'] . "</button>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-ellipsis' title='" . $buttonset->{"ellipsis"} . "'>" . $buttonset->{"ellipsis"}['caption'] . "</button>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-std-code-1' title='" . $buttonset->{"std-code-1"} . "'>&lt;&lt;1&gt;&gt;</button>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-std-code-g1' title='" . $buttonset->{"std-code-g1"} . "'>&lt;&lt;1{}&gt;&gt;</button>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-player' title='" . $buttonset->{"player"} . "'>" . $buttonset->{"player"}['caption'] . "</button>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-npc' title='" . $buttonset->{"npc"} . "'>" . $buttonset->{"npc"}['caption'] . "</button>";         
         $html .= "<button class='ui-state-default buttonset' id='buttonset-delete' title='" . $buttonset->{"delete"} . "'>" . $buttonset->{"delete"}['caption'] . "</button>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-cancel' title='" . $buttonset->{"cancel"} . "'>" . $buttonset->{"cancel"}['caption'] . "</button>";
         $html .= "<br />";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-fr' title='" . $buttonset->{"copyfr"} . "'>" . $buttonset->{"copyfr"}['caption'] . "</button>";
         $html .= "<button class='ui-state-default buttonset' id='buttonset-en' title='" . $buttonset->{"copyen"} . "'>" . $buttonset->{"copyen"}['caption'] . "</button>";
         if ($usertype !== "TRANSLATOR")
         {
            $html .= "<button class='ui-state-default buttonset' id='buttonset-revise' title='" . $buttonset->{"revise"} . "'>" . $buttonset->{"revise"}['caption'] . "</button>";
            $html .= "<button class='ui-state-default buttonset' id='buttonset-unrevise' title='" . $buttonset->{"unrevise"} . "'>" . $buttonset->{"unrevise"}['caption'] . "</button>";
         }
         if ($usertype === "ADMIN")
         {
            $html .= "<button class='ui-state-default buttonset' id='buttonset-lock' title='" . $buttonset->{"lock"} . "'>" . $buttonset->{"lock"}['caption'] . "</button>";
            $html .= "<button class='ui-state-default buttonset' id='buttonset-unlock' title='" . $buttonset->{"unlock"} . "'>" . $buttonset->{"unlock"}['caption'] . "</button>";
            $html .= "<button class='ui-state-default buttonset' id='buttonset-annul' title='" . $buttonset->{"annul"} . "'>" . $buttonset->{"annul"}['caption'] . "</button>";
            $html .= "<button class='ui-state-default buttonset' id='buttonset-dispute' title='" . $buttonset->{"dispute"} . "'>" . $buttonset->{"dispute"}['caption'] . "</button>";
         }
         $html .= "</td></tr>";
         
         $array = array("html" => $html);
         echo \json_encode($array);
      }
   }

}
