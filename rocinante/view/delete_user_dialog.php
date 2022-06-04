<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
   <!-- This is the HTML code for the dialog that is shown when 'Delete user' button is clicked. -->
   <div id="delete-user-dialog" title="<?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"users"}->{"delete-user-button"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <p><?php echo $l10n->{"dialog"}->{"user"}->{"confirm-deletion"} ?></p>
      </div>
      <div class="ui-state-error ui-corner-all warning-padding" id="wrong-data">
         <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong></strong></p>
      </div>
   </div>
