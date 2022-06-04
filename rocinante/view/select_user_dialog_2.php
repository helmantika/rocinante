<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>
   <!-- This is the HTML code for the dialog that is used to select an addreessee for a message. -->
   <div id="select-user-dialog-2" title="<?php echo $l10n->{"dialog"}->{"mail"}->{"select-user"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <div class="scrollable-dialog">
            <table id="brief-usertable-2"></table>
         </div>
      </div>
   </div>


