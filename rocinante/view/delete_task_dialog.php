<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
   <!-- This is the HTML code for the dialog that is shown when 'Delete task' button is clicked. -->
   <div id="delete-task-dialog" title="<?php echo $fp->{"tabs"}->{"tasks"}->{"delete-task-button"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <p></p>
      </div>
   </div>
