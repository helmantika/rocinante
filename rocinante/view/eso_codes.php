<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>
   <!-- This is the HTML code for the dialog that is shown when an ESO code is clicked. -->
   <div id="eso-code-dialog">
      <div class="ui-widget-content ui-corner-all content"></div>
   </div>
