<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>

<div id="database-setup">
   <div class="ui-state-highlight ui-corner-all header-spacing warning">
      <p><?php echo $l10n->{"setup"}->{"database-setup-text"} ?></p>
   </div>
   <div class="ui-widget ui-corner-all content">
      <form action="#">
         <fieldset>
            <table style="text-align: center">
               <tbody> 
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"setup"}->{"host"} ?></td>
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="host" id="host"></td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"setup"}->{"database"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="database" id="database"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"setup"}->{"user"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="username" id="username"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"setup"}->{"password"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="password" name="password" id="password"></td>
                  </tr> 
                  <tr>
                     <td></td>
                     <td><span id="creating-database"><img src="images/ajax-loader.gif" alt="loading"/><strong><?php echo $l10n->{"setup"}->{"wait"} ?></strong></span></td>
                  </tr>
               </tbody> 
            </table>
         </fieldset>
      </form>
   </div>
   <div class="ui-state-error ui-corner-all warning-padding" id="failed-connection">
      <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong><span id="mysql-error"></span></strong></p>
   </div>
   <div class="ui-state-highlight ui-corner-all warning-padding" id="right-connection">
      <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong><span id="mysql-ok"></span></strong></p>
   </div>
   <div class="ui-widget-header ui-corner-all" id="info">
      <button class="ui-state-default" id="test-connection-button"><?php echo (string) $l10n->{"setup"}->{"test-button"} ?></button>
      <button class="ui-state-default goto-files-setup-button"><?php echo (string) $l10n->{"setup"}->{"next-button"} ?></button>
   </div>
</div>

<div id="files-setup">
   <div class="ui-state-highlight ui-corner-all header-spacing warning">
      <p><?php echo $l10n->{"setup"}->{"files-setup-text"} ?></p>
   </div>
   <div class="ui-widget ui-corner-all content">
      <form action="#">
         <fieldset>
            <table>
               <tbody>  
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"client-en"} ?></td>
                     <td>
                        <input type="text" name="clienten" id="clienten" disabled>
                        <button class="ui-state-default" id="select-en-client"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                     </td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"pregame-en"} ?></td>
                     <td>
                        <input type="text" name="pregameen" id="pregameen" disabled>
                        <button class="ui-state-default" id="select-en-pregame"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                     </td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"lang-en"} ?></td>
                     <td>
                        <input type="text" name="langen" id="langen" disabled>
                        <button class="ui-state-default" id="select-en-lang"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                     </td>
                  </tr>
                  <tr>
                     <td class="right-text"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"client-fr"} ?></td>
                     <td>
                        <input type="text" name="clientfr" id="clientfr" disabled>
                        <button class="ui-state-default" id="select-fr-client"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                     </td>
                  </tr>

                  <tr>
                     <td class="right-text"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"pregame-fr"} ?></td>
                     <td>
                        <input type="text" name="pregamefr" id="pregamefr" disabled>
                        <button class="ui-state-default" id="select-fr-pregame"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                     </td>
                  </tr>
                  <tr>
                     <td class="right-text"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"lang-fr"} ?></td>
                     <td>
                        <input type="text" name="langfr" id="langfr" disabled>
                        <button class="ui-state-default" id="select-fr-lang"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                     </td>
                  </tr>
                  <tr id="browse-update-files-row-1">
                     <td></td>
                     <td><input type="file" name="browse-en-client" id="browse-en-client" /></td>
                     <td></td>
                     <td><input type="file" name="browse-fr-client" id="browse-fr-client" /></td>
                  </tr>
                  <tr id="browse-update-files-row-2">
                     <td></td>
                     <td><input type="file" name="browse-en-pregame" id="browse-en-pregame" /></td>
                     <td></td>
                     <td><input type="file" name="browse-fr-pregame" id="browse-fr-pregame" /></td>
                  </tr> 
                  <tr id="browse-update-files-row-3">
                     <td></td>
                     <td><input type="file" name="browse-en-lang" id="browse-en-lang" /></td>
                     <td></td>
                     <td><input type="file" name="browse-fr-lang" id="browse-fr-lang" /></td>
                  </tr>   
               </tbody> 
            </table>
         </fieldset>
      </form>
   </div>
   <div class="ui-widget-header ui-corner-all" id="info">
      <button class="ui-state-default goto-target-language-setup-button"><?php echo (string) $l10n->{"setup"}->{"next-button"} ?></button>
   </div>
</div>

<div id="target-language-setup">
   <div class="ui-state-highlight ui-corner-all header-spacing warning">
      <p><?php echo $l10n->{"setup"}->{"target-language-text"} ?></p>
   </div>
   <div class="ui-widget ui-corner-all content">
      <form action="#">
         <fieldset>
            <table>
               <tbody>  
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"setup"}->{"target-language"} ?></td> 
                     <td><input style="box-sizing: border-box; width: 180px" type="text" name="targetlang" id="targetlang"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo (string) $l10n->{"setup"}->{"csv-file"} ?></td>
                     <td>
                        <input style="box-sizing: border-box; width: 180px" type="text" name="csvfile" id="csvfile" disabled>
                        <button class="ui-state-default" id="select-csv-file"><?php echo (string) $l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                     </td>
                  </tr>
                  <tr>
                     <td></td>
                     <td><span id="uploading-files"><img src="images/ajax-loader.gif" alt="loading"/><strong><?php echo $l10n->{"setup"}->{"wait"} ?></strong></span></td>
                  </tr>
                  <tr id="browse-csv-file-row">
                     <td></td>
                     <td><input type="file" name="browse-csv-file" id="browse-csv-file" /></td>
                  </tr> 
               </tbody> 
            </table>
         </fieldset>
      </form>
   </div>
   <div class="ui-state-error ui-corner-all warning-padding" id="failed-load">
      <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong><span id="load-error"></span></strong></p>
   </div>
   <div class="ui-widget-header ui-corner-all" id="info">
      <button class="ui-state-default" id="prev-button"><?php echo (string) $l10n->{"setup"}->{"prev-button"} ?></button>
      <button class="ui-state-default goto-admin-setup-button"><?php echo (string) $l10n->{"setup"}->{"next-button"} ?></button>
   </div>
</div>

<div id="admin-setup">
   <div class="ui-state-highlight ui-corner-all header-spacing warning">
      <p><?php echo $l10n->{"setup"}->{"admin-text"} ?></p>
   </div>
   <div class="ui-widget ui-corner-all content">
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
               </tbody> 
            </table>
         </fieldset>
      </form>
   </div>
   <div class="ui-state-error ui-corner-all warning-padding" id="bad-user">
      <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong><span id="user-error"></span></strong></p>
   </div>
   <div class="ui-widget-header ui-corner-all" id="info">
      <button class="ui-state-default goto-end-setup-button"><?php echo (string) $l10n->{"setup"}->{"next-button"} ?></button>
   </div>
</div>

<div id="end-setup">
   <div class="ui-state-highlight ui-corner-all header-spacing warning">
      <p><?php echo $l10n->{"setup"}->{"end-setup-text"} ?></p>
   </div>
   <div class="ui-widget ui-corner-all content"></div>
   <div class="ui-widget-header ui-corner-all" id="info">
      <button class="ui-state-default" id="finish-button"><?php echo (string) $l10n->{"setup"}->{"finish-button"} ?></button>
   </div>
</div>