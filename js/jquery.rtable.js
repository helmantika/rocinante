/* 
 * The MIT License (MIT)
 * Rocinante's Table. Copyright (c) 2016 Jorge Rodríguez Santos
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
    * Rocinante's table (rtable) is a yet another jQuery UI plug-in to render HTML tables. It allows
    * column ordering and, optionally, a pager can be included.
    * 
    * @class rtable
    * @constructor
    * 
    * @param {Object} [l10n] An object that contains localization data
    * @param {Object} l10n.format An object that contains numeric marks, and date/time format.
    * @param {String} l10n.format.thousands Thousands separator; e.g., a dot.
    * @param {String} l10n.format.decimal Decimal separator; e.g. a comma.
    * @param {String} l10n.format.dateTime Date and time format given in Moment.js format; e.g, 
    * "MM/DD/YYYY HH:mm:ss".
    * @param {String} l10n.format.date Date format given in Moment.js format; e.g, "MM/DD/YYYY".
    * @param {String} l10n.format.time Time format given in Moment.js format; e.g, "HH:mm:ss".
    * @param {Object} [l10n.pager] An object that contains localized string for pager. The goal is 
    * to build a string like "Page 1 of 372", "Page 1/372", etc.
    * @param {String} l10n.pager.page "Page" label for pager; e.g., "Page", "Pag", and so on.
    * @param {String} l10n.pager.of "of" label for pager; e.g., "of", "/", and so on.
    * @param {Object} [selection] An object that stores the selected row and the actions to do on 
    * selection and unselection. 
    * @param {Array} selection.row <b>OUT</b> An array with data from a selected row. Data is stored
    * in the format that is shown in the table.
    * @param {Boolean} selection.mark Defines whether a selected row must be marked.
    * @param {Boolean} selection.hand Defines whether mouse pointer has to be changed into a hand.
    * @param {Function} selection.onSelect What to do when a row in selected.
    * @param {Function} selection.onUnselect What to do when a row in unselected.
    * @param {Object} [pager] An object with information for pager.
    * @param {Integer} pager.current Current page.
    * @param {Integer} pager.total Total number of pages.
    * @param {Function} pager.onFirst How to go to the first page.
    * @param {Function} pager.onPrev How to go to the previous page.
    * @param {Function} pager.onNext How to go to the next page.
    * @param {Function} pager.onEnd How to go to the last page.
    * @param {Array} hidden An array with zero-based indices of columns that will be hidden. 
    * @param {Function} decorator A function that will be applied after table sorting.
    * @param {Function} sorter 
    * @param {String} pagerid
    */
   $.fn.rtable = function (l10n, selection, pager, hidden, decorator, sorter, pagerid) {
      // Table ID with class rtable.
      var tableid = "#" + $(this).attr("id");
      // Array where table data is stored.
      var array = [];
      // Selected row given by a jQuery selector.
      var selectedRow = null;
      //
      var row = 0;
      // Regular expresion for a real number with localized separators.
      var decimalRegex = new RegExp("([-+]?\\d{0,3})(\\" + l10n.format.thousands +
                                    "?(\\d{3}))?(\\" + l10n.format.decimal +
                                    "(\\d+))?\\s?%?");
      // Regular expresion for a hexadecimal number.
      var hexadecimalRegex = /0[x|X][0-9abcdefABCDEF]+/;
      
      // Set standard option if someone is not specified.
      l10n.format.thousands = l10n.format.thousands || ',';
      l10n.format.decimal = l10n.format.decimal || '.';
      l10n.format.dateTime = l10n.format.dateTime || 'MM/DD/YYYY HH:mm:ss';
      l10n.format.date = l10n.format.date || 'MM/DD/YYYY';
      l10n.format.time = l10n.format.time || 'HH:mm:ss';
      l10n.pager.page = l10n.pager.page || 'Page';
      l10n.pager.of = l10n.pager.of || 'of';
      hidden = hidden || [];
      decorator = decorator || function () {};
      sorter = sorter || function () {};
      pagerid = pagerid || "pager";

      /**
       * Parses a string by looking for a real number, a hexadecimal number, or a date/time.
       * 
       * @private
       * @method parseText
       * 
       * @param {String} text A string to parse.
       * @return {Object} An object with two properties: <b>type</b> whose values can be 
       * <i>string</i>, <i>number</i>, and <i>date</i>; and <b>value</b> that stores the numerical 
       * value of the string, or null when the string does not store a number or a date. A numerical
       * date is given as the number of seconds since 00:00 - 1/1/1970.
       */
      function parseText(text) {
         var result = {type: "string", value: false};
         var match = text.match(decimalRegex);
         
         if (match[0].length > 0 && match[0] === text) {
            result.type = "number";
            result.value = parseFloat(match[0].replace(decimalRegex, "$1$3.$5"));
         } else {
            match = text.match(hexadecimalRegex);
            if (match !== null && match[0] === text) {
               result.type = "number";
               result.value = parseInt(match[0], 10);
            } else {
               match = [moment(text, l10n.format.dateTime, true),
                        moment(text, l10n.format.date, true),
                        moment(text, l10n.format.time, true)];
               for (var i = 0; i < match.length; i++) {
                  if (match[i].isValid()) {
                     result.type = "date";
                     result.value = match[i].unix();
                     break;
                  }
               }
            }
         }

         return result;
      };

      /**
       * Sorts the table in ascending order by means of a column data.
       * 
       * @private
       * @method ascendingSort
       * 
       * @param {Integer} masterColumn A column index starting at 0.
       */
      function ascendingSort(masterColumn) {
         array.sort(function (e1, e2) {
            var result = -Number.MAX_NUMBER;
            if (e1[masterColumn].number !== undefined && e2[masterColumn].number !== undefined) {
               if (e1[masterColumn].number.type === "string") {
                  result = e1[masterColumn].text.localeCompare(e2[masterColumn].text); 
               } else {
                  result = e1[masterColumn].number.value - e2[masterColumn].number.value;
               }
            }
            return result;
         });
      };

      /**
       * Sorts the table in descending order by means of a column data.
       * 
       * @private
       * @method descendingSort
       * 
       * @param {Integer} masterColumn A column index starting at 0.
       */
      function descendingSort(masterColumn) {
         array.sort(function (e1, e2) {
            var result = -Number.MAX_NUMBER;
            if (e1[masterColumn].number !== undefined && e2[masterColumn].number !== undefined) {
               if (e1[masterColumn].number.type === "string") {
                  result = e2[masterColumn].text.localeCompare(e1[masterColumn].text); 
               } else {
                  result = e2[masterColumn].number.value - e1[masterColumn].number.value;
               }
            }
            return result;
         });
      };

      /**
       * Sorts the table by means of a column data. If the last sorting was ascending then next 
       * sorting will be descending, and vice versa. 
       * 
       * The selected row will be unselected before sorting. After sorting, it modifies DOM table to
       * show sorting results. Also it sets text align, and icons.
       * 
       * @private
       * @method sortTable
       * 
       * @param {Object} event A column index given in <b>event.data.masterColumn</b>.
       */
      function sortTable(event) {
         var masterColumn = event.data.masterColumn;
         
         // If there is no pager or there is only 1 page then sorting is done by the client.
         if (!$(tableid).parent().find("#" + pagerid).length || parseInt($(tableid).parent().find("#total").text() > 1)) { 
            // Unselect row.
            if (selectedRow !== null) {
               selectedRow.prevAll().removeClass("ui-state-active");
               selectedRow.removeClass("ui-state-active");
               selectedRow.nextAll().removeClass("ui-state-active");
               selectedRow = null;
               selection.row = [];
               selection.onUnselect();
            }

            // Sort data.
            if (array[0][masterColumn].isAscending) {
               ascendingSort(masterColumn);
            } else {
               descendingSort(masterColumn);
            }
            array[0][masterColumn].isAscending = !array[0][masterColumn].isAscending;

            // Modify table with sorting results.
            for (var i = 0, max = $(tableid).find("tr:first").children().last().index(); i <= max; i++) {
               row = 0;
               $(tableid).find("tr").each(function () {
                  var tableElement = $(this).children().eq(i);
                  tableElement.css({"text-align": array[row][i].align});
                  tableElement.text(array[row][i].text);
                  if (row === 0) {
                     var icon = tableElement.find("span");
                     icon.removeClass("ui-icon-triangle-2-n-s");
                     icon.removeClass("ui-icon-triangle-1-n");
                     icon.removeClass("ui-icon-triangle-1-s");
                     if (i !== masterColumn) {
                        icon.addClass("ui-icon-triangle-2-n-s");
                     } else if (array[row][i].isAscending) {
                        icon.addClass("ui-icon-triangle-1-s");
                     } else {
                        icon.addClass("ui-icon-triangle-1-n");
                     }
                  }
                  row++;
               });
            }
            
            // Decorate table.
            decorator();
         } else {
            // When there are several pages, sorting is done by the server.
            sorter(masterColumn);
         }
      };
      
      /**
       * Creates and appends a pager to the table. The pager shows the current page and total pages.
       * Four buttons, (begin, previous, next, end), are also available.
       * 
       * @private
       * @method createPager
       */
      function createPager() {
         var code = '<div class="ui-widget-header" id="' + pagerid + '" style="padding: 5px; margin: 0px 1px">\n' +
                    '<form id="pager-form">\n' + 
                    '   ' + l10n.pager.page + '\n' + 
                    '   <input type="text" name="page" value="1" style="width: 32px"/>\n' + 
                    '   <input type="submit" name="connect">\n' + 
                    '   ' + l10n.pager.of + ' <span id="total" style="margin-right: 15px">37</span>\n' + 
                    '   <button class="ui-state-default" id="button-first" style="vertical-align: middle;"></button>\n' + 
                    '   <button class="ui-state-default" id="button-prev" style="vertical-align: middle;"></button>\n' + 
                    '   <button class="ui-state-default" id="button-next" style="vertical-align: middle;"></button>\n' + 
                    '   <button class="ui-state-default" id="button-end" style="vertical-align: middle;"></button>\n' + 
                    '</form>\n' + 
                    '</div>\n';
         var parent = $(tableid).parent();

         // Bind events and show the pager.
         if (!parent.find("#" + pagerid).length) { 
            $(tableid).after(code);
            parent = parent.find("#" + pagerid);
            parent.find("#button-first").button({icons: {primary: "ui-icon-seek-first"}, 
                                                 text: false}).click(pager.onClickFirst);
            parent.find("#button-prev").button({icons: {primary: "ui-icon-seek-prev"},
                                                text: false}).click(pager.onClickPrev);
            parent.find("#button-next").button({icons: {primary: "ui-icon-seek-next"},
                                                text: false}).click(pager.onClickNext);
            parent.find("#button-end").button({icons: {primary: "ui-icon-seek-end"},
                                               text: false}).click(pager.onClickEnd);
            parent.find("#pager-form").on("submit", {text: parent.find("input[name=page]")}, pager.onSubmitPage);
            parent.find("input[type=submit]").hide();
            parent.find("input[name=page]").val(pager.current);
            parent.find("#total").text(pager.total);
            parent.find("#pager").show();
         } else {
            parent = parent.find("#" + pagerid);
            parent.find("input[name=page]").val(pager.current);
            parent.find("#total").text(pager.total);
         }
      };
      
      /**
       * Marks a row when the mouse pointer is over.
       * 
       * @private
       * @method onMouseOver
       */
      function onMouseOver() {
         $(this).prevAll().addClass("ui-state-hover").css("font-weight", "normal");
         $(this).addClass("ui-state-hover").css("font-weight", "normal");
         $(this).nextAll().addClass("ui-state-hover").css("font-weight", "normal");         
      };
      
      /**
       * Unmarks a row when the mouse pointer is out.
       * 
       * @private
       * @method onMouseOut
       */
      function onMouseOut() {
         $(this).prevAll().removeClass("ui-state-hover");
         $(this).removeClass("ui-state-hover");
         $(this).nextAll().removeClass("ui-state-hover");
      }
      
      /**
       * Selects a row when mouse left button is clicked.
       * 
       * @private
       * @method onMouseClick
       * 
       * @param {Object} event A table data element given in <b>event.data.td</b>.
       */
      function onMouseClick(event) {
         var tableElement = event.data.td;
         
         if (selectedRow !== null) {
            if (selection.mark) {
               selectedRow.prevAll().removeClass("ui-state-active");
               selectedRow.removeClass("ui-state-active");
               selectedRow.nextAll().removeClass("ui-state-active");
            }
         }
         if (selectedRow !== tableElement || !selection.mark) {
            if (selection.mark) {
               $(this).prevAll().addClass("ui-state-active");
               $(this).addClass("ui-state-active");
               $(this).nextAll().addClass("ui-state-active");
            }

            selectedRow = tableElement;
            selection.row = [];
            // prevAll() returns elements from the selected one to the beginning.
            tableElement.prevAll().each(function () {
               selection.row.unshift($(this).text());
            });
            selection.row.push(tableElement.text());
            // nextAll() returns elements from the selected one to the end.
            tableElement.nextAll().each(function () {
               selection.row.push($(this).text());
            });

            selection.onSelect();
         } else {
            selectedRow = null;
            selection.row = [];
            selection.onUnselect();
         }
      };
      
      /**
       * Clear the selected row, if there is anyone.
       * 
       * @private
       * @method clear
       */
      selection.onClear(function () {
         if (selectedRow !== null) {
            if (selection.mark) {
               selectedRow.prevAll().removeClass("ui-state-active");
               selectedRow.removeClass("ui-state-active");
               selectedRow.nextAll().removeClass("ui-state-active");
            }
            selectedRow = null;
            selection.row = [];
            selection.onUnselect();
         }
      });
      
      // Create a matrix that stores table data. First index specifies a row, second one specifies a
      // column. Matrix stores two kind of objects: one for headers, the other one for cell data. 
      // 
      // Header object has two properties: the first one specifies the last type of sorting 
      // (ascending or descending), while the second one specifies text align. 
      // 
      // Cell data object stores its content, an equivalent numerical value, and text align.
      for (var i = 0, max = $(tableid).find("tr:first").children().last().index(); i <= max; i++) {
         row = 0;
         $(tableid).find("tr").each(function () {
            if (array[row] === undefined) {
               array[row] = [];
            }
            var tableElement = $(this).children().eq(i);
            if (row === 0) {
               array[row].push({isAscending: true,
                                align: undefined});
            } else {
               var number = parseText(tableElement.text());
               var length = array[row].push({text: tableElement.text(),
                                             number: number,
                                             align: number.type === "string" ? "left" : "right"});
               // Header must have same align that data.
               if (row === 1) {
                  array[0][length - 1].align = array[1][length - 1].align;
               }
            }
            row++;
         });
      }

      // If the pager must be created, do it now.
      if (pager !== undefined) {
         createPager();
      }

      // Modify DOM table by means of setting text align and icon, and binding click event with 
      // each column header and function sortTable.
      for (var i = 0, max = $(tableid).find("tr:first").children().last().index(); i <= max; i++) {
         row = 0;
         $(tableid).find("tr").each(function () {
            var tableElement = $(this).children().eq(i);
            if (hidden.indexOf(i) !== -1) {
               tableElement.hide();
            } else {
               tableElement.css("text-align", array[row][i].align);
               tableElement.text(array[row][i].align.text);
               // Header
               if (row === 0) {
                  tableElement.addClass("ui-widget-header");
                  tableElement.css("cursor", "pointer");
                  tableElement.css("vertical-align", "baseline");
                  tableElement.append('<span class="ui-icon ui-icon-triangle-2-n-s" style="float: right"></span>');
                  tableElement.on("click", {masterColumn: i}, sortTable);
               }
               // Content
               else {
                  tableElement.addClass("ui-widget-content");
                  if (selection.hand) {
                     tableElement.css("cursor", "pointer");
                  }
                  if (row % 2 === 0) {
                     tableElement.addClass("odd-" + l10n.misc.theme);
                  }
                  if (selection !== undefined) {
                     tableElement.on("mouseover", onMouseOver);
                     tableElement.on("mouseout", onMouseOut);
                     tableElement.on("click", {td: tableElement}, onMouseClick);
                  }
               }
               row++;
            }
         });
      }
      
      // Decorate table.
      decorator();
   };
})(jQuery);
