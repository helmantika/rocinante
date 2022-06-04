<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>
   <!-- This is the HTML code for the dialog that is shown when 'Edit ESO table description' button is clicked. -->
   <div id="update-esotable-dialog" title="<?php echo $l10n->{"dialog"}->{"esotable"}->{"title"} ?>">
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"frontpage"}->{"tabs"}->{"master-table"}->{"table"}->{"id"} ?></td>
                     <td><input type="text" name="tablename" id="tablename" style="box-sizing: border-box; width: 300px" disabled></td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"frontpage"}->{"tabs"}->{"master-table"}->{"table"}->{"description"} ?></td> 
                     <td><input type="text" name="description" id="description" style="box-sizing: border-box; width: 300px"></td>
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"esotable"}->{"type"} ?></td>
                     <td>
                       <select name="type" id="select-table-type"></select>
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
