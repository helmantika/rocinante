<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>

         <div class="ui-widget-header ui-corner-all header-spacing header-padding">
            <strong><?php echo $fp->{"tabs"}->{"tasks"}->{"assigner-task-table-caption"} ?></strong>
         </div>
         <table id="assigner-task-table">
            <!-- Task table with those ones assigned by the user is here -->
         </table>