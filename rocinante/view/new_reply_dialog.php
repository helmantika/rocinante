<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
   <!-- This is the HTML code for the dialog that is shown when 'New task' button is clicked. -->
   <div id="new-reply-dialog" title="<?php echo $fp->{"tabs"}->{"mail"}->{"new-reply-button"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table style="width: 100%">
               <tbody> 
                  <tr> 
                     <td class="right-text" style="width: 10%"><?php echo $l10n->{"dialog"}->{"mail"}->{"to"} ?></td>
                     <td style="width: 80%"><input type="text" name="addressees" id="addressees" style="box-sizing: border-box; width: 100%"></td>
                     <td style="width: 10%"><button class="ui-state-default" id="search-user-table-2"><?php echo $l10n->{"dialog"}->{"mail"}->{"select"} ?></button></td>
                  </tr>    
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"mail"}->{"subject"} ?></td>
                     <td colspan="2"><input type="text" name="subject" id="subject" style="box-sizing: border-box; width: 100%"></td> 
                  </tr>
                  <tr style="display: none"> 
                     <td colspan="3"><input type="text" name="chatid" id="chatid"></td> 
                  </tr>
                  <tr style="display: none"> 
                     <td colspan="3"><input type="text" name="isdraft" id="isdraft"></td> 
                  </tr>
                  <tr>
                     <td colspan="3"><textarea name="body" id="body" rows="20" style="box-sizing: border-box; width: 100%; resize: none;"></textarea></td>
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


<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

