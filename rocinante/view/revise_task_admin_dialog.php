<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
   <!-- This is the HTML code for the dialog that is shown when 'Modify task' button is clicked. -->
   <div id="revise-task-admin-dialog" title="<?php echo $fp->{"tabs"}->{"tasks"}->{"revise-task-button"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody>
                  <tr>
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"task"}->{"user"} ?></td>
                     <td>
                       <select name="user" id="select-task-user"></select>
                     </td>
                  </tr>
                  <tr style="display: none">
                     <td></td>
                     <td><input style="box-sizing: border-box; width: 190px" type="hidden" name="taskid" id="taskid"></td>
                  </tr>
               </tbody>
               </table>
            </fieldset>
         </form>
      </div>
      <div id="dialog-ajax-loader" style="text-align: center;"><img src="images/ajax-loader-bar.gif"></div>
      <div class="ui-state-error ui-corner-all warning-padding" id="wrong-data">
         <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong></strong></p>
      </div>
   </div>
