<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>
   <!-- This is the HTML code for the dialog that is shown when 'Maintenance' button is clicked. -->
   <div id="maintenance-mode-dialog" title="<?php echo $l10n->{"dialog"}->{"maintenance"}->{"title"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"maintenance"}->{"status"} ?></td> 
                     <td>
                       <select name="mode" id="select-maintenance-mode">
                          <?php echo '<option value="ON">' . $l10n->{"dialog"}->{"maintenance"}->{"activate"} ?>
                          <?php echo '<option value="OFF">' . $l10n->{"dialog"}->{"maintenance"}->{"deactivate"} ?>
                       </select>
                     </td> 
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"maintenance"}->{"message"} ?></td> 
                     <td><textarea name="message" id="message" rows="5" style="box-sizing: border-box; width: 210px; resize: none;"></textarea></td>
                  </tr> 
               </tbody> 
               </table>
            </fieldset>
         </form>   
      </div>
      <div class="ui-state-error ui-corner-all warning-padding" id="wrong-data">
         <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong></strong></p>
      </div>
   </div>

