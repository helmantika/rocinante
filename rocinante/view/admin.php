<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
      <div id="admin">
         <div id="admin-tabs">
            <ul>
               <li><a href="#admin-users"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"users"}['text'] ?></a></li>
               <li><a href="#admin-stats"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"stats"}['text'] ?></a></li>
               <li><a href="#admin-tasks"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"tasks"}['text'] ?></a></li>
               <li><a href="#admin-addon"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}['text'] ?></a></li>
               <li><a href="#admin-update"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}['text'] ?></a></li>
            </ul>

            <div id="admin-users">
               <button class="ui-state-default" id="new-user-button"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"users"}->{"new-user-button"} ?></button>
               <button class="ui-state-default" id="modify-user-button"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"users"}->{"modify-user-button"} ?></button>
               <button class="ui-state-default" id="delete-user-button"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"users"}->{"delete-user-button"} ?></button>

               <div class="ui-state-highlight ui-corner-all header-spacing warning-padding" id="admin-users-info">
                  <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
               </div>

               <table class="header-spacing" id="user-table">
                  <!-- Table of users is here -->
               </table>
            </div>

            <div id="admin-stats">
               <table id="stats-table">
                  <!-- Table of stats is here -->
               </table>
            </div>

            <div id="admin-tasks">
               <button class="ui-state-default" id="new-task-admin-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"new-task-button"} ?></button>
               <button class="ui-state-default" id="open-task-admin-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"open-task-button"} ?></button>
               <button class="ui-state-default" id="reassign-task-admin-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"reassign-task-button"} ?></button>
               <button class="ui-state-default" id="revise-task-admin-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"revise-task-button"} ?></button>
               <button class="ui-state-default" id="delete-task-admin-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"delete-task-button"} ?></button>

               <div class="ui-state-highlight ui-corner-all header-spacing warning-padding" id="admin-task-info">
                  <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
               </div>

               <table class="header-spacing" id="admin-task-table">
                  <!-- Task table for admins is here -->
               </table>
            </div>

            <div id="admin-addon" class="content">               
               <form action="#">
                  <fieldset>
                     <table>
                     <tbody> 
                        <tr> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"extra-files"} ?></td>
                           <td>
                             <select name="type" id="select-extrafiles-mode">
                                <option value="NO_EXTRAFILES"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"extrafiles-mode"}->{"no-extrafiles"} ?></option>
                                <option value="ADD_EXTRAFILES"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"extrafiles-mode"}->{"add-extrafiles"} ?></option>
                                <option value="DELETE_EXTRAFILES"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"extrafiles-mode"}->{"delete-extrafiles"} ?></option>
                             </select>
                           </td> 
                        </tr>  
                        <tr id="extrafiles-row"> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"zip-file"} ?></td>
                           <td>
                              <input type="text" name="extrafiles" id="extrafiles" disabled>
                              <button class="ui-state-default" id="select-extra-files"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"browse"} ?></button>
                           </td>
                        </tr>
                        <tr> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"client-header-file"} ?></td>
                           <td>
                              <input type="text" name="clientheader" id="clientheader" disabled>
                              <button class="ui-state-default" id="select-clientheader-file"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"browse"} ?></button>
                           </td>
                        </tr>
                        <tr> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"pregame-header-file"} ?></td>
                           <td>
                              <input type="text" name="pregameheader" id="pregameheader" disabled>
                              <button class="ui-state-default" id="select-pregameheader-file"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"browse"} ?></button>
                           </td>
                        </tr>
                        <tr> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"version"} ?></td> 
                           <td>
                              <input type="text" name="version" id="version">
                              <span id="creating-addon" style="vertical-align: middle;"><img src="images/ajax-loader.gif" alt="loading"/><strong><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"creating-addon"} ?></strong></span>
                              <span id="generate-addon"><button class="ui-state-default" id="generate-addon-button"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"addon"}->{"generate-button"} ?></button></span>
                           </td>
                        </tr> 
                        <tr id="browse-extra-files-row">
                           <td><input type="file" accept="application/zip,application/x-zip,application/x-zip-compressed" name="browse-extra-files" id="browse-extra-files" /></td>
                           <td>
                              <input type="file" accept="text/plain" name="browse-clientheader-file" id="browse-clientheader-file" />
                              <input type="file" accept="text/plain" name="browse-pregameheader-files" id="browse-pregameheader-file" />
                           </td>
                        </tr>
                     </tbody> 
                     </table>
                  </fieldset>
               </form>
               
               <div class="ui-state-error ui-corner-all header-spacing warning-padding" id="admin-addon-info">
                  <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
               </div>
            </div>

            <div id="admin-update" class="content">
               <form action="#">
                  <fieldset>
                     <table>
                     <tbody>  
                        <tr>
                           <td colspan="4" style="text-align: center;">
                              <span id="maintenance-mode"><button class="ui-state-default" id="maintenance-mode-button"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"maintenance"} ?></button></span>
                           </td>
                        </tr>
                        <tr><td colspan="4" style="padding-top: 25px"></td></tr>
                        <tr> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"client-en"} ?></td>
                           <td>
                              <input type="text" name="clienten" id="clienten" disabled>
                              <button class="ui-state-default" id="select-en-client"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                           </td>
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"client-fr"} ?></td>
                           <td>
                              <input type="text" name="clientfr" id="clientfr" disabled>
                              <button class="ui-state-default" id="select-fr-client"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                           </td>
                        </tr>
                        <tr> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"pregame-en"} ?></td>
                           <td>
                              <input type="text" name="pregameen" id="pregameen" disabled>
                              <button class="ui-state-default" id="select-en-pregame"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                           </td>
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"pregame-fr"} ?></td>
                           <td>
                              <input type="text" name="pregamefr" id="pregamefr" disabled>
                              <button class="ui-state-default" id="select-fr-pregame"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                           </td>
                        </tr>
                        <tr> 
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"lang-en"} ?></td>
                           <td>
                              <input type="text" name="langen" id="langen" disabled>
                              <button class="ui-state-default" id="select-en-lang"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                           </td>
                           <td class="right-text"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"lang-fr"} ?></td>
                           <td>
                              <input type="text" name="langfr" id="langfr" disabled>
                              <button class="ui-state-default" id="select-fr-lang"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"browse"} ?></button>
                           </td>
                        </tr>
                        <tr><td colspan="4" style="padding-top: 25px"></td></tr>
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
                        <tr>
                           <td colspan="4" style="text-align: center;">
                              <span id="update-database"><button class="ui-state-default" id="update-database-button"><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"update"} ?></button></span>
                              <span id="updating-database" style="vertical-align: middle;"><img src="images/ajax-loader.gif" alt="loading"/><strong><?php echo $fp->{"tabs"}->{"admin"}->{"tabs"}->{"update"}->{"updating"} ?></strong></span>                              
                           </td>
                        </tr>
                     </tbody> 
                     </table>
                  </fieldset>
               </form>
               
               <div class="ui-state-error ui-corner-all header-spacing warning-padding" id="admin-update-info">
                  <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
               </div>
            </div>
         </div>
      </div>
