<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
   <!-- This is the HTML code for the dialog that is shown when button that shows the user name is clicked. -->
   <div id="update-current-user-dialog" title="<?php echo $l10n->{"dialog"}->{"modify-user-account"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"username"} ?></td>
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="username" id="username" disabled></td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"password"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="password" name="password" id="password"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"password-again"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="password" name="passwordv" id="passwordv"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"name"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="name" id="name"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"gender"} ?></td> 
                     <td>
                       <select name="gender" id="select-user-gender">
                          <option value="MALE"><?php echo $l10n->{"gender"}->{"male"} ?></option>
                          <option value="FEMALE"><?php echo $l10n->{"gender"}->{"female"} ?></option>
                       </select>
                     </td> 
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"email"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="email" id="email"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"ui-theme"} ?></td> 
                     <td>
                       <select name="theme" id="select-ui-theme"></select>
                     </td> 
                  </tr> 
               </tbody> 
               </table>
            </fieldset>
         </form>
         <div class="ui-state-highlight ui-corner-all header-spacing warning-padding">
            <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong><?php echo $l10n->{"dialog"}->{"user"}->{"warning"} ?></strong></p>
         </div>
      </div>
      <div class="ui-state-error ui-corner-all warning-padding" id="wrong-data">
         <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong></strong></p>
      </div>
   </div>
