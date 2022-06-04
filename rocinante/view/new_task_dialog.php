<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/SessionRegistry.php';

$session = \rocinante\command\SessionRegistry::instance();
$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
   <!-- This is the HTML code for the dialog that is shown when 'New task' button is clicked. -->
   <div id="new-task-dialog" title="<?php echo $fp->{"tabs"}->{"tasks"}->{"new-task-button"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"task"}->{"type"} ?></td>
                     <td>
                       <select name="type" id="select-task-type">
                          <option value="TRANSLATION"><?php echo $l10n->{"task-type"}->{"translation"} ?></option>
                          <?php if ($session->getType() !== "TRANSLATOR") { echo "<option value='REVISION'>" . $l10n->{"task-type"}->{"revision"} . "</option>"; } ?>
                          <option value="UPDATING"><?php echo $l10n->{"task-type"}->{"updating"} ?></option>
                       </select>
                     </td> 
                  </tr>    
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"task"}->{"user"} ?></td>
                     <td>
                       <select name="user" id="select-task-user"></select>
                     </td> 
                  </tr>
                  <tr style="display: none"> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"task"}->{"table"} ?></td>
                     <td><input style="box-sizing: border-box; width: 190px" type="hidden" name="tableid" id="tableid"></td>
                  </tr>
                  <tr id="table-row"> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"task"}->{"table"} ?></td>
                     <td>
                        <input style="box-sizing: border-box; width: 190px" type="text" name="tablenumber" id="tablenumber" disabled>
                        <button class="ui-state-default" id="search-task-table"><?php echo $l10n->{"dialog"}->{"task"}->{"select"} ?></button>
                     </td>
                  </tr>
                  <tr id="building-mode-row"> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"task"}->{"task-building-mode"} ?></td> 
                     <td>
                       <select name="mode" id="select-task-mode">
                          <?php echo '<option value="' . $l10n->{"task-mode"}->{"status"}['value'] . '">' . $l10n->{"task-mode"}->{"status"} ?>
                          <?php echo '<option value="' . $l10n->{"task-mode"}->{"offset-inc"}['value'] . '">' . $l10n->{"task-mode"}->{"offset-inc"} ?>
                          <?php echo '<option value="' . $l10n->{"task-mode"}->{"offset-exc"}['value'] . '">' . $l10n->{"task-mode"}->{"offset-exc"} ?>
                       </select>
                     </td> 
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"task"}->{"count"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 190px" type="text" name="count" id="count"></td>
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


