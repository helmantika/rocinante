<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$l10n = $helper->getL10n();
?>
   <!-- This is the HTML code for the dialog that is shown when 'New term' button is clicked. -->
   <div id="new-term-dialog" title="<?php echo $l10n->{"dialog"}->{"glossary"}->{"add-title"} ?>">
      <div class="ui-state-highlight ui-corner-all header-spacing warning-padding">
         <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong><?php echo $l10n->{"dialog"}->{"glossary"}->{"glossary-warning"} ?></strong></p>
         <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><?php echo $l10n->{"dialog"}->{"glossary"}->{"new-term-warning"} ?></p>
      </div>
      <div class="ui-widget-content ui-corner-all content">
         <form action="#">
            <fieldset>
               <table>
               <tbody> 
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"term"} ?></td>
                     <td>
                        <input type="text" name="term" id="term" style="box-sizing: border-box; width: 350px">
                        <button class="ui-state-default" id="count-new-singular-term"><?php echo $l10n->{"dialog"}->{"glossary"}->{"count"} ?></button>
                     </td>
                  </tr>
                  <tr> 
                     <td class="right-text"><?php echo $l10n->{"dialog"}->{"glossary"}->{"plural-term"} ?></td>
                     <td>
                        <input type="text" name="plural" id="plural" style="box-sizing: border-box; width: 350px" placeholder="<?php echo $l10n->{"dialog"}->{"glossary"}->{"optional"} ?>">
                        <button class="ui-state-default" id="count-new-plural-term"><?php echo $l10n->{"dialog"}->{"glossary"}->{"count"} ?></button>
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

