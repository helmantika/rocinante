<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
   <!-- This is the HTML code for the dialog that is shown when 'Modify user' button is clicked. -->
   <div id="update-user-dialog" title="<?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"users"}->{"modify-user-button"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"username"} ?></td>
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="username" id="username"></td>
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
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"type"} ?></td>
                     <td>
                       <select name="type" id="select-user-type">
                          <option value="TRANSLATOR"><?php echo $l10n->{"user-type"}->{"male-translator"} ?></option>
                          <option value="ADVISOR"><?php echo $l10n->{"user-type"}->{"male-advisor"} ?></option>
                          <option value="ADMIN"><?php echo $l10n->{"user-type"}->{"male-admin"} ?></option>
                       </select>
                     </td> 
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
                  <tr id="advisor-row"> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"user"}->{"advisor"} ?></td> 
                     <td>
                       <select name="advisor" id="select-advisor-name"></select>
                     </td> 
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
