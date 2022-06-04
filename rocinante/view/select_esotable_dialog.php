<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>
   <!-- This is the HTML code for the dialog that is used to select an ESO table for a task. -->
   <div id="select-esotable-dialog" title="<?php echo $l10n->{"dialog"}->{"task"}->{"select-table"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <div class="scrollable-dialog">
            <table id="brief-esotable"></table>
         </div>
      </div>
   </div>
   
   