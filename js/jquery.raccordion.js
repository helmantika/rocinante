/*
 * The MIT License (MIT)
 * Language Table. Copyright (c) 2016 Jorge Rodríguez Santos
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute,
 * sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or
 * substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
 * NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
(function ($) {

   /**
    * Rocinante's accordion (raccordion) is a jQuery UI plug-in, that includes a pager, to render 
    * email messages.
    *
    * @class raccordion
    * @constructor
    *
    * @param {Object} app
    * @param {Object} [pager] An object with information for pager.
    * @param {Integer} pager.current Current page.
    * @param {Integer} pager.total Total number of pages.
    * @param {Function} pager.onFirst How to go to the first page.
    * @param {Function} pager.onPrev How to go to the previous page.
    * @param {Function} pager.onNext How to go to the next page.
    * @param {Function} pager.onEnd How to go to the last page.
    */
   $.fn.raccordion = function (app, selection, pager) {
      // Table ID with class rtable.
      var divid = "#" + $(this).attr("id");
      // Localization.
      var l10n = app.getLocalization().getTable();
      
      // Creates or updates an accordion.
      if ($(divid).hasClass("ui-accordion")) {
         $(divid).accordion("refresh");
      } else {
         $(divid).accordion({
            heightStyle: "content",
            collapsible: true,
            active: false
         });  
      }
      selection.onUnselect();
      
      $(divid).on("accordionactivate", function (event, ui) {
         if (ui.newHeader.length === 0) {
            selection.object = {};
            selection.onUnselect();
         } else {
            var tr = ui.newHeader.find("table").find("tr").children();
            var addressees = [];
            for (var i = 0, max = tr.eq(1).find("strong").length; i < max; i++) {
               addressees.push(tr.eq(1).find("strong").eq(i).text());
            }
            selection.object = {selector: tr,
                                subject: tr.eq(0).text(),
                                addressees: addressees.join(', '),
                                mailid: tr.eq(2).text(),
                                chatid: tr.eq(3).text(),
                                box: tr.eq(4).text(),
                                isRead: tr.eq(5).text(),
                                body: ui.newPanel.text() };
            selection.onSelect();
         }
      });
      
      /**
       * Creates and appends a pager to the table. The pager shows the current page and total pages.
       * Four buttons, (begin, previous, next, end), are also available.
       * @method createPager
       * @private
       */
      function createPager() {
         var code = '<div class="ui-widget-header pager" style="padding: 5px; margin: 20px 1px">\n' +
                    '<form class="pager-form">\n' +
                    '   ' + l10n.pager.page + '\n' +
                    '   <input class="pager-current-page" type="text" name="page" value="1" style="width: 32px"/>\n' +
                    '   <input class="pager-submit" type="submit" name="connect">\n' +
                    '   ' + l10n.pager.of + ' <span class="pager-total" style="margin-right: 15px">37</span>\n' +
                    '   <button class="ui-state-default pager-button-first" style="vertical-align: middle;"></button>\n' +
                    '   <button class="ui-state-default pager-button-prev" style="vertical-align: middle;"></button>\n' +
                    '   <button class="ui-state-default pager-button-next" style="vertical-align: middle;"></button>\n' +
                    '   <button class="ui-state-default pager-button-end" style="vertical-align: middle;"></button>\n' +
                    '</form>\n' +
                    '</div>\n';
         var div = $(divid);

         // Bind events and show the pager.
         if (div.find(".pager").length === 0) {
            div.append(code);
            div.find(".pager-button-first").button({icons: {primary: "ui-icon-seek-first"},
                                                    text: false}).click(pager.onClickFirst);
            div.find(".pager-button-prev").button({icons: {primary: "ui-icon-seek-prev"},
                                                   text: false}).click(pager.onClickPrev);
            div.find(".pager-button-next").button({icons: {primary: "ui-icon-seek-next"},
                                                   text: false}).click(pager.onClickNext);
            div.find(".pager-button-end").button({icons: {primary: "ui-icon-seek-end"},
                                                  text: false}).click(pager.onClickEnd);
            div.find(".pager-form").on("submit", {text: $(this).find("input[name=page]")}, pager.onSubmitPage);
            div.find(".pager-submit").hide();
            div.find(".pager").show();

         }
         div.find(".pager-current-page").val(pager.current);
         div.find(".pager-total").text(pager.total);
      };
      createPager();
   };
})(jQuery);
