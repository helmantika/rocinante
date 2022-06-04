<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/SessionRegistry.php';

$session = \rocinante\command\SessionRegistry::instance();
$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>
   <!-- This is the HTML code for the dialog that is shown when user clicks on a glossary term. -->
   <div id="update-term-dialog" title="<?php echo $l10n->{"dialog"}->{"glossary"}->{"update-title"} ?>">
      <div class="ui-state-highlight ui-corner-all header-spacing warning-padding">
         <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><?php echo $l10n->{"dialog"}->{"glossary"}->{"update-term-warning"} ?></p>
      </div>
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody> 
                  <tr> 
                     <td></td>
                     <td><input type="text" name="termid" id="termid" style="display: none"></td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"term"} ?></td>
                     <td>
                         <input type="text" name="term" id="term" style="box-sizing: border-box; width: 350px" disabled>
                         <button class="ui-state-default" id="count-modify-singular-term"><?php echo $l10n->{"dialog"}->{"glossary"}->{"count"} ?></button>
                         <?php if ($session->getType() !== "TRANSLATOR") { echo "<button class='ui-state-default' id='delete-term'>" . $l10n->{"dialog"}->{"glossary"}->{"delete"} . "</button>"; } ?>
                     </td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"plural-term"} ?></td>
                     <td>
                        <input type="text" name="plural" id="plural" style="box-sizing: border-box; width: 350px" placeholder="<?php echo $l10n->{"dialog"}->{"glossary"}->{"optional"} ?>">
                        <button class="ui-state-default" id="count-modify-plural-term"><?php echo $l10n->{"dialog"}->{"glossary"}->{"count"} ?></button>
                     </td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"translation"} ?></td> 
                     <td><input type="text" name="translation" id="translation" style="box-sizing: border-box; width: 350px" placeholder="<?php echo $l10n->{"dialog"}->{"glossary"}->{"only-singular"} ?>"></td>
                  </tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"type"} ?></td>
                     <td>
                       <select name="type" id="select-term-type"></select>
                     </td> 
                  </tr> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"note"} ?></td> 
                     <td><textarea name="note" id="note" rows="5" style="box-sizing: border-box; width: 350px; resize: none;"></textarea></td>
                  </tr> 
                  <?php if ($session->getType() === "ADMIN") { echo "<tr>"; } else { echo "<tr style='display: none'>"; } ?>
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"locked"} ?></td>
                     <td>
                        <select name="locked" id="select-locked-term">
                           <option value="0"><?php echo $l10n->{"dialog"}->{"no"} ?></option>
                           <option value="1"><?php echo $l10n->{"dialog"}->{"yes"} ?></option>
                        </select>
                     </td> 
                  </tr> 
               </tbody> 
               </table>
            </fieldset>
            <div class="ui-state-highlight ui-corner-all header-spacing warning-padding" id="counter-info">
               <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
            </div>
         </form>   
      </div>
      <div id="dialog-ajax-loader" style="text-align: center;"><img src="images/ajax-loader-bar.gif"></div>
      <div class="ui-state-error ui-corner-all warning-padding" id="wrong-data">
         <p><span class="ui-icon ui-icon-alert warning-icon-pos"></span><strong></strong></p>
      </div>
   </div>


