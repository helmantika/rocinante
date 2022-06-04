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
    * Translation table (langtable) is a jQuery UI plug-in to render HTML tables where users
    * translate. It includes a pager.
    *
    * @class langtable
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
    * @param {String} typeOfTask TRANSLATION, REVISION, UPDATING, or GLOSSARY.
    */
   $.fn.langtable = function (app, pager, typeOfTask) {
      // Table ID with class rtable.
      var tableid = "#" + $(this).attr("id");
      // Localization.
      var l10n = app.getLocalization().getTable();
      // Column indices.
      var column = {tableid: 0, textid: 1, seqid: 2, type: 3, flags: 4, string: 5, status: 6, notes: 7, updated: 8, translated: 9, revised: 10, locked: 11, disputed: 12};
      // Hidden columns.
      var hidden = [column.updated, column.translated, column.revised, column.locked, column.disputed];
      // Row iterators.
      var row = 0, group = 0;
      // Glossary and Updating task texts are handle in a special way.
      var isSpecialTask = typeOfTask === "UPDATING" || typeOfTask === "GLOSSARY";

      /**
       * Inserts a given string at caret position.
       * @param {String} text Text to paste.
       */
      function pasteTextAtCaret(text) {
         if (window.getSelection) {
            var selection = window.getSelection();
            if (selection.getRangeAt && selection.rangeCount) {
               var range = selection.getRangeAt(0);
               range.deleteContents();

               // Range.createContextualFragment() would be useful here but is
               // only relatively recently standardized.
               var element = document.createElement("div");
               element.textContent = text;
               var fragment = document.createDocumentFragment(), node, lastNode;
               while ((node = element.firstChild)) {
                  lastNode = fragment.appendChild(node);
               }
               range.insertNode(fragment);

               // Preserve the selection
               if (lastNode) {
                  range = range.cloneRange();
                  range.setStartAfter(lastNode);
                  range.collapse(true);
                  selection.removeAllRanges();
                  selection.addRange(range);
               }
            }
         }
      }
      
      /**
       * Inserts a given string at caret position.
       * @param {String} html HTML string to paste.
       */
      function pasteHtmlAtCaret(html) {
         if (window.getSelection) {
            var selection = window.getSelection();
            if (selection.getRangeAt && selection.rangeCount) {
               var range = selection.getRangeAt(0);
               range.deleteContents();

               // Range.createContextualFragment() would be useful here but is
               // only relatively recently standardized.
               var element = document.createElement("div");
               element.innerHTML = html;
               var fragment = document.createDocumentFragment(), node, lastNode;
               while ((node = element.firstChild)) {
                  lastNode = fragment.appendChild(node);
               }
               range.insertNode(fragment);

               // Preserve the selection
               if (lastNode) {
                  range = range.cloneRange();
                  range.setStartAfter(lastNode);
                  range.collapse(true);
                  selection.removeAllRanges();
                  selection.addRange(range);
               }
            }
         }
      }
      
      /**
       * Stores data of a translated string and defines its behavior by means of a state machine. 
       * Also it manages the glossary, and responds to the events emitted by buttons.
       * 
       * @class Translation
       * @constructor
       * 
       * @param {jQuery} tableElement A jQuery object that selects a <td> element.
       */
      function Translation(tableElement) {
         /**
          * A jQuery object that selects a <td> element where translation string is.
          * @property tableElement
          * @type {jQuery}
          */
         this.tableElement = tableElement;
         
         if (tableElement !== undefined) {
            var td = tableElement.parent().parent().prev().prev().children("td[rowspan]");
            this.tableid = parseInt(td.eq(column.tableid).text(), 16);
            this.textid = this.tableid !== 0 ? parseInt(td.eq(column.textid).text(), 10) : td.eq(column.textid).text();
            this.seqid = parseInt(td.eq(column.seqid).text(), 10);
         }

         /**
          * Changes translation state.
          * @method change
          * @param {Function} state A state.
          */
         this.change = function (state) {
            this.currentState = state;
            this.currentState.handle();
         };
         
         /**
          * Starts translation edition.
          * @method startEditing
          */
         this.startEditing = function () {
            this.change(this.state.startEditing(this));
         };
         
         /**
          * Finish translation edition.
          * @method finishEditing
          */
         this.finishEditing = function () {
            this.change(this.state.finishEditing(this));
         };
         
         /**
          * Creates an object that should be filled with data used by updating a translation string.
          * @method createEmptyObject
          * @return {Object} An object with the following properties: tableid, textid, seqid, text,
          * isUpdated, isTranslated, isRevised, isLocked, and isDisputed.
          */
         this.createEmptyObject = function () {
            return {tableid: null, textid: null, seqid: null, text: null, isUpdated: null, 
                    isTranslated: null, isRevised: null, isLocked: null, isDisputed: null};
         };
      }
      /**
       * Defines whether translation identifiers are numbers or not.
       * @return {Boolean} 
       */
      Translation.prototype.isNaN = function () {
         return isNaN(this.tableid) || (this.tableid !== 0 && isNaN(this.textid)) || isNaN(this.seqid);
      };
      /**
       * Defines whether translation identifiers are undefined or not.
       * @return {Boolean} 
       */
      Translation.prototype.isUndefined = function () {
         return this.tableid === undefined || this.textid === undefined || this.seqid === undefined;
      };
      /**
       * Sets the translation identifiers to undefined.
       */
      Translation.prototype.nullify = function () {
         this.tableid = undefined;
         this.textid = undefined;
         this.seqid = undefined;
      };
      /**
       * Defines whether translation identifiers of this object are equal to another object ones. 
       * @param {Translation} translation A translation object.
       * @return {Boolean}
       */
      Translation.prototype.equals = function (translation) {
         return this.tableid === translation.tableid && this.textid === translation.textid && this.seqid === translation.seqid;
      };
      /**
       * Inserts a string at the end of translation string.
       * @param {String} content The string to append.
       */
      Translation.prototype.append = function (content) {
         pasteTextAtCaret(content);
      };
      /**
       * Events that are emitted when a string is being translated.
       * @property events
       * @type {Object}
       */
      Translation.prototype.events = {
         /**
          * Finishes string edition.
          * @method onSaveButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onSaveButtonClick: function (e) {
            e.data.translation.finishEditing();
            previousTranslation.nullify();
            e.preventDefault();
         },
         /**
          * Cancels string edition.
          * @method onCancelButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onCancelButtonClick: function (e) {
            var t = e.data.translation;
            if (t.previous.length > 0) {
               t.tableElement.html(t.previous);
            } else {
               t.tableElement.html("<br />");
            }
            e.data.translation.finishEditing();
            previousTranslation.nullify();
            e.preventDefault();
         },
         /**
          * Deletes translation string.
          * @method onDeleteButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onDeleteButtonClick: function (e) {
            var t = e.data.translation;
            t.tableElement.html("<br />");
            t.tableElement.focus();
            e.preventDefault();
         },
         /**
          * Inserts ellipsis character at the end of translation string.
          * @method onEllipsisButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onEllipsisButtonClick: function (e) {
            pasteTextAtCaret("…");
            e.preventDefault();
         },
         /**
          * Inserts a standard ESO code at the end of translation string.
          * @method onStandardCodeButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onStandardCodeButtonClick: function (e) {
            var code = e.data.number;
            pasteTextAtCaret("<<" + code + ">>");
            e.preventDefault();
         },
         /**
          * Inserts a gender ESO code at the end of translation string.
          * @method onGenderCodeButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onGenderCodeButtonClick: function (e) {
            var code = e.data.number;
            pasteTextAtCaret("<<" + code + "{}>>");
            e.preventDefault();
         },
         /**
          * Inserts player ESO code at the end of translation string.
          * @method onPlayerCodeButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onPlayerCodeButtonClick: function (e) {
            pasteTextAtCaret("<<player{}>>");
            e.preventDefault();
         },
         /**
          * Inserts npc ESO code at the end of translation string.
          * @method onNpcCodeButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onNpcCodeButtonClick: function (e) {
            pasteTextAtCaret("<<npc{}>>");
            e.preventDefault();
         },
         /**
          * Copies French text into translation box.
          * @method onFrCodeButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onFrCodeButtonClick: function (e) {
            var t = e.data.translation;
            var fr = t.tableElement.parent().parent().prev().prev().prev().children().eq(5).text();
            fr = fr.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
            pasteHtmlAtCaret(fr);
            e.preventDefault();
         },
         /**
          * Copies English text into translation box.
          * @method onEnCodeButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onEnCodeButtonClick: function (e) {
            var t = e.data.translation;
            var en = t.tableElement.parent().parent().prev().prev().children().eq(1).text();
            en = en.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
            pasteHtmlAtCaret(en);
            e.preventDefault();
         },
         /**
          * Sets the translation string as revised, and requests its updating to the server.
          * @method onReviseButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onReviseButtonClick: function (e) {
            var t = e.data.translation;
            if (t.tableElement.text().length > 0) {
               var data = t.createEmptyObject();
               data.selector = t.tableElement;
               data.tableid = t.tableid;
               data.textid = t.textid;
               data.seqid = t.seqid;
               data.text = t.tableElement.html();
               if (isSpecialTask) {
                  data.isUpdated = 1;
               }
               data.isRevised = 1;
               app.updateLang(data);

               t.removeGlossaryAndButtonSet(t);
               if (isSpecialTask) {
                  t.updatedColumn.text("1");
               }
               t.revisedColumn.text("1");
               t.state.updateStatusColumn(t);
               previousTranslation.nullify();
               e.preventDefault();
            }
         },
         /**
          * Sets the translation string as not revised, and requests its updating to the server.
          * @method onUnreviseButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onUnreviseButtonClick: function (e) {
            var t = e.data.translation;
            if (t.tableElement.text().length > 0) {
               var data = t.createEmptyObject();
               data.selector = t.tableElement;
               data.tableid = t.tableid;
               data.textid = t.textid;
               data.seqid = t.seqid;
               data.text = t.tableElement.html();
               data.isRevised = 0;
               app.updateLang(data);

               t.removeGlossaryAndButtonSet(t);
               t.revisedColumn.text("0");
               t.state.updateStatusColumn(t);
               previousTranslation.nullify();
               e.preventDefault();
            }
         },
         /**
          * Sets the translation string as locked, and requests its updating to the server.
          * @method onLockButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onLockButtonClick: function (e) {
            var t = e.data.translation;
            var data = t.createEmptyObject();
            data.selector = t.tableElement;
            data.tableid = t.tableid;
            data.textid = t.textid;
            data.seqid = t.seqid;
            data.text = t.tableElement.html();
            if (isSpecialTask) {
               data.isUpdated = 1;
            }
            data.isLocked = 1;
            app.updateLang(data);

            t.removeGlossaryAndButtonSet(t);
            if (isSpecialTask) {
               t.updatedColumn.text("1");
            }
            t.lockedColumn.text("1");
            t.state.updateStatusColumn(t);
            previousTranslation.nullify();
            e.preventDefault();
         },
         /**
          * Sets the translation string as not locked, and requests its updating to the server.
          * @method onUnlockButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onUnlockButtonClick: function (e) {
            var t = e.data.translation;
            var data = t.createEmptyObject();
            var isBlank = false;
            
            data.selector = t.tableElement;
            data.tableid = t.tableid;
            data.textid = t.textid;
            data.seqid = t.seqid;
            data.text = t.tableElement.html();
            isBlank = data.text.replace(/([<br>|<br/>|<br />|\n])+$/, "").length === 0;
            if (isBlank) {
               data.isUpdated = 0;
               data.isTranslated = 0;
               data.isRevised = 0;
            }
            data.isLocked = 0;
            data.isDisputed = 0;   
            app.updateLang(data);

            t.removeGlossaryAndButtonSet(t);
            if (isBlank) {
               t.updatedColumn.text("0");
               t.translatedColumn.text("0");
               t.revisedColumn.text("0");
            }
            t.lockedColumn.text("0");
            t.disputedColumn.text("0");
            t.state.updateStatusColumn(t);
            previousTranslation.nullify();
            e.preventDefault();
         },
         /**
          * Sets the translation string as translated, revised, and locked. Also, a string update is 
          * requested to the server.
          * @method onAnnulButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onAnnulButtonClick: function (e) {
            var t = e.data.translation;
            var en = t.tableElement.parent().parent().prev().prev().children().eq(1).text();
            en = en.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
            pasteHtmlAtCaret(en);
            e.data.translation.finishEditing();
            
            var data = t.createEmptyObject();
            data.selector = t.tableElement;
            data.tableid = t.tableid;
            data.textid = t.textid;
            data.seqid = t.seqid;
            data.text = en;
            if (isSpecialTask) {
               data.isUpdated = 1;
            }
            data.isTranslated = 1;
            data.isRevised = 1;
            data.isLocked = 1;
            app.updateLang(data);

            if (isSpecialTask) {
               t.updatedColumn.text("1");   
            }
            t.translatedColumn.text("1");
            t.revisedColumn.text("1");
            t.lockedColumn.text("1");
            t.state.updateStatusColumn(t);
            previousTranslation.nullify();
            e.preventDefault();
         },
         /**
          * Sets the translation string as disputed, and requests its updating to the server.
          * @method onDisputeButtonClick
          * @param {Event} e Stores a translation object in data.
          */
         onDisputeButtonClick: function (e) {
            var t = e.data.translation;
            var data = t.createEmptyObject();
            data.selector = t.tableElement;
            data.tableid = t.tableid;
            data.textid = t.textid;
            data.seqid = t.seqid;
            data.text = t.tableElement.html();
            data.isLocked = 1;
            data.isDisputed = 1;   
            app.updateLang(data);

            t.removeGlossaryAndButtonSet(t);
            t.lockedColumn.text("1");
            t.disputedColumn.text("1");
            t.state.updateStatusColumn(t);
            previousTranslation.nullify();
            e.preventDefault();
         }
      };
      Translation.prototype.state = {
         glossary: '<tr><td class="ui-widget-header"></td><td class="ui-widget-content"></td></tr>',
         buttonSet: app.getTranslationButtonSet(),
         startEditing: function (translation) {
            this.handle = function () {
               if (translation.tableElement !== undefined) {
                  var mainRow = translation.tableElement.parent().parent().prev().prev();
                  translation.previous = translation.tableElement.html();
                  translation.statusColumn = mainRow.children().eq(column.status);
                  translation.updatedColumn = mainRow.children().eq(column.updated);
                  translation.translatedColumn = mainRow.children().eq(column.translated);
                  translation.revisedColumn = mainRow.children().eq(column.revised);
                  translation.lockedColumn = mainRow.children().eq(column.locked);
                  translation.disputedColumn = mainRow.children().eq(column.disputed);

                  // Prepare the selected string for editing if it's not locked.
                  if (translation.lockedColumn.text() === "0") {
                     translation.tableElement.attr("contenteditable", "true");
                     translation.tableElement.focus();
                  }
                  mainRow.children("td[rowspan]").each(function () {
                     $(this).attr("rowspan", "5");
                  });

                  // Insert glossary and buttons.
                  translation.tableElement.parent().parent().before(translation.state.glossary);
                  translation.tableElement.parent().parent().after(translation.state.buttonSet);

                  // Color rows that belong to an odd group.
                  if (translation.tableElement.hasClass("odd")) {
                     translation.tableElement.parent().parent().prev().children().eq(1).addClass("odd");
                  }

                  // Apply UI theme to buttons.
                  app.createButton($("#buttonset-cancel").button().on("click", {translation: translation}, translation.events.onCancelButtonClick), "ui-icon-cancel");
                  if (translation.lockedColumn.text() === "0") { // Not locked
                     app.createButton($("#buttonset-save").button().on("click", {translation: translation}, translation.events.onSaveButtonClick), "ui-icon-disk");
                     app.createButton($("#buttonset-delete").button().on("click", {translation: translation}, translation.events.onDeleteButtonClick), "ui-icon-trash");
                     app.createButton($("#buttonset-ellipsis").button().on("click", {translation: translation}, translation.events.onEllipsisButtonClick), "ui-icon-pencil");
                     app.createButton($("#buttonset-std-code-1").button().on("click", {translation: translation, number: "1"}, translation.events.onStandardCodeButtonClick), "ui-icon-pencil");
                     app.createButton($("#buttonset-std-code-g1").button().on("click", {translation: translation, number: "1"}, translation.events.onGenderCodeButtonClick), "ui-icon-pencil");
                     app.createButton($("#buttonset-player").button().on("click", {translation: translation}, translation.events.onPlayerCodeButtonClick), "ui-icon-pencil");
                     app.createButton($("#buttonset-npc").button().on("click", {translation: translation}, translation.events.onNpcCodeButtonClick), "ui-icon-pencil");
                     app.createButton($("#buttonset-fr").button().on("click", {translation: translation}, translation.events.onFrCodeButtonClick), "ui-icon-copy");
                     app.createButton($("#buttonset-en").button().on("click", {translation: translation}, translation.events.onEnCodeButtonClick), "ui-icon-copy");
                  } else {
                     $("#buttonset-save").button().hide();
                     $("#buttonset-delete").button().hide();
                     $("#buttonset-ellipsis").button().hide();
                     $("#buttonset-std-code-1").button().hide();
                     $("#buttonset-std-code-g1").button().hide();
                     $("#buttonset-player").button().hide();
                     $("#buttonset-npc").button().hide();
                     $("#buttonset-fr").button().hide();
                     $("#buttonset-en").button().hide();
                  }
                  // If string is translated, show Revise button.
                  if (translation.translatedColumn.text() === "1" && translation.revisedColumn.text() === "0" && translation.lockedColumn.text() === "0") {
                     app.createButton($("#buttonset-revise").button().on("click", {translation: translation}, translation.events.onReviseButtonClick), "ui-icon-circle-check");
                  } else {
                     $("#buttonset-revise").button().hide();
                  }
                  // If string is revised, show Unrevise and Lock buttons.
                  if (translation.translatedColumn.text() === "1" && translation.revisedColumn.text() === "1" && translation.lockedColumn.text() === "0") {
                     app.createButton($("#buttonset-unrevise").button().on("click", {translation: translation}, translation.events.onUnreviseButtonClick), "ui-icon-circle-close");
                     app.createButton($("#buttonset-lock").button().on("click", {translation: translation}, translation.events.onLockButtonClick), "ui-icon-locked");
                  } else {
                     $("#buttonset-unrevise").button().hide();
                     $("#buttonset-lock").button().hide();
                  }
                  // If string is locked, show Unlock button.
                  if (translation.lockedColumn.text() === "1" || translation.disputedColumn.text() === "1") {
                     app.createButton($("#buttonset-unlock").button().on("click", {translation: translation}, translation.events.onUnlockButtonClick), "ui-icon-unlocked");
                  } else {
                     $("#buttonset-unlock").button().hide();
                  }
                  // If string is not locked, show Annul and Dispute buttons.
                  if (translation.lockedColumn.text() === "0") {
                     app.createButton($("#buttonset-annul").button().on("click", {translation: translation}, translation.events.onAnnulButtonClick), "ui-icon-closethick");
                     app.createButton($("#buttonset-dispute").button().on("click", {translation: translation}, translation.events.onDisputeButtonClick), "ui-icon-alert");
                  } else {
                     $("#buttonset-annul").button().hide();
                     $("#buttonset-dispute").button().hide();
                  }

                  // Request terms to the glossary.
                  app.listGlossaryForText(translation.tableElement.parent().parent().prev().children().eq(1),
                                          translation.tableid, translation.textid, translation.seqid);
               }
            };
            return this;
         },
         finishEditing: function (translation) {
            this.handle = function () {
               var previous = translation.previous;
               var current = translation.tableElement.html();
               
               // Remove line breaks from the end of strings.
               previous = previous.replace(/([<br>|<br/>|<br />])+$/, "");
               previous = previous.replace(/&nbsp;/gi," ");
               current = current.replace(/([<br>|<br/>|<br />])+$/, "");
               current = current.replace(/&nbsp;/gi," ");
               
               // Reset row aspect.
               translation.removeGlossaryAndButtonSet(translation);

               // Change the translation state.
               if (previous.length === 0) {
                  if (current.length === 0) {
                     translation.state.updateStatusColumn(translation);
                  } else {
                     translation.change(Translation.prototype.state.translationCreated(translation));
                  }
               } else {
                  if (current.length === 0) {
                     translation.change(Translation.prototype.state.translationDeleted(translation));
                  } else {
                     if (previous === current) {
                        translation.change(Translation.prototype.state.translationUnchanged(translation));
                     } else {
                        translation.change(Translation.prototype.state.translationUpdated(translation));
                     }
                  }
               }
            };
            return this;
         },
         translationCreated: function (translation) {
            this.handle = function () {
               var data = translation.createEmptyObject();
               data.selector = translation.tableElement;
               data.tableid = translation.tableid;
               data.textid = translation.textid;
               data.seqid = translation.seqid;
               data.text = translation.tableElement.html();
               if (isSpecialTask) {
                  data.isUpdated = 1;
               }
               data.isTranslated = 1;
               
               if (isSpecialTask) {
                  translation.updatedColumn.text("1");   
               }
               translation.translatedColumn.text("1");
               translation.state.updateStatusColumn(translation);
               app.updateLang(data);
            };
            return this;
         },
         translationDeleted: function (translation) {
            this.handle = function () {
               var data = translation.createEmptyObject();
               data.selector = translation.tableElement;
               data.tableid = translation.tableid;
               data.textid = translation.textid;
               data.seqid = translation.seqid;
               data.isUpdated = 0;
               data.isTranslated = 0;
               data.isRevised = 0;
               data.isLocked = 0;
               data.isDisputed = 0;

               translation.updatedColumn.text("0");
               translation.translatedColumn.text("0");
               translation.revisedColumn.text("0");
               translation.lockedColumn.text("0");
               translation.tableElement.html("<br />");
               translation.state.updateStatusColumn(translation);
               app.updateLang(data);
            };
            return this;
         },
         translationUnchanged: function (translation) {
            this.handle = function () {
               var data = translation.createEmptyObject();
               data.selector = translation.tableElement;
               data.tableid = translation.tableid;
               data.textid = translation.textid;
               data.seqid = translation.seqid;
               data.text = translation.previous;
               data.isTranslated = 1;

               translation.translatedColumn.text("1");
               translation.state.updateStatusColumn(translation);
               app.updateLang(data);
            };
            return this;
         },
         translationUpdated: function (translation) {
            this.handle = function () {
               var data = translation.createEmptyObject();
               data.selector = translation.tableElement;
               data.tableid = translation.tableid;
               data.textid = translation.textid;
               data.seqid = translation.seqid;
               data.text = translation.tableElement.html();
               if (isSpecialTask) {
                  data.isUpdated = 1;
               }
               data.isTranslated = 1;
               
               if (isSpecialTask) {
                  translation.updatedColumn.text("1");   
               }
               translation.translatedColumn.text("1");
               translation.state.updateStatusColumn(translation);
               app.updateLang(data);
            };
            return this;
         },
         updateStatusColumn: function (translation) {
            if (translation.disputedColumn.text() === "1") {
               translation.statusColumn.addClass("disputed").attr("title", l10n.status.disputed).tooltip();
            } else if (translation.lockedColumn.text() === "1") {
               if (isSpecialTask && translation.updatedColumn.text() !== "0") {
                  translation.statusColumn.addClass("locked-and-updated");
               } else {
                  translation.statusColumn.addClass("locked");
               }
               translation.statusColumn.attr("title", l10n.status.locked).tooltip();
            } else if (translation.revisedColumn.text() === "1") {
               if (isSpecialTask && translation.updatedColumn.text() !== "0") {
                  translation.statusColumn.addClass("revised-and-updated");
               } else {
                  translation.statusColumn.addClass("revised");
               }
               translation.statusColumn.attr("title", l10n.status.revised).tooltip();
            } else if (translation.translatedColumn.text() === "1") {
               if (isSpecialTask && translation.updatedColumn.text() !== "0") {
                  translation.statusColumn.addClass("not-revised-and-updated");
               } else {
                  translation.statusColumn.addClass("not-revised");
               }
               translation.statusColumn.attr("title", l10n.status.notRevised).tooltip();
            } else {
               translation.statusColumn.addClass("not-translated").attr("title", l10n.status.notTranslated).tooltip();
            }
         }
      };
      Translation.prototype.removeGlossaryAndButtonSet = function (translation) {
         // Reset translation status column.
         translation.statusColumn.removeClass("disputed").removeClass("locked").removeClass("locked-and-updated").removeClass("revised").removeClass("revised-and-updated").removeClass("not-revised").removeClass("not-revised-and-updated").removeClass("not-translated");

         // Remove button set and glossary rows.
         $("#buttonset-save").button("destroy");
         $("#buttonset-cancel").button("destroy");
         $("#buttonset-delete").button("destroy");
         $("#buttonset-ellipsis").button("destroy");
         $("#buttonset-revise").button("destroy");
         $("#buttonset-unrevise").button("destroy");
         $("#buttonset-lock").button("destroy");
         $("#buttonset-unlock").button("destroy");
         $("#buttonset-annul").button("destroy");
         $("#buttonset-dispute").button("destroy");
         translation.tableElement.parent().parent().prev().remove();
         translation.tableElement.parent().parent().next().remove();
         translation.tableElement.attr("contenteditable", "false");
         translation.tableElement.parent().parent().prev().prev().children("td[rowspan]").each(function () {
            $(this).attr("rowspan", "3");
         });
      };

      // Translation string ID that is being edited.
      var previousTranslation = new Translation();

      /**
       * A comment written for a translated string.
       * 
       * @class Note
       * @constructor
       * 
       * @param {jQuery} tableElement A jQuery object that selects a <td> element.
       */
      function Note(tableElement) {
         /**
          * A jQuery object that selects a <td> element where translation string is.
          * @property tableElement
          * @type {jQuery}
          */
         this.tableElement = tableElement;
         
         if (tableElement !== undefined) {
            var td = tableElement.parent().parent().children();
            this.tableid = parseInt(td.eq(column.tableid).text(), 16);
            this.textid = this.tableid !== 0 ? parseInt(td.eq(column.textid).text(), 10) : td.eq(column.textid).text();
            this.seqid = parseInt(td.eq(column.seqid).text(), 10);
         }
         
         /**
          * Starts note edition.
          * @method startEditing
          */
         this.startEditing = function () {
            if (this.tableElement !== undefined) {
               this.previous = this.tableElement.html();

               // Prepare the selected string for editing.
               this.tableElement.attr("contenteditable", "true");
               this.tableElement.focus();
            }
         };

         /**
          * Finishes note edition.
          * @method finishEditing
          */
         this.finishEditing = function () {
            var data = this.createEmptyObject();
            data.text = this.tableElement.html();
            this.tableElement.attr("contenteditable", "false");
            app.updateLangNotes(data);
         };
         
         /**
          * Cancels note edition.
          * @method cancelEditing
          * @param {Note} note The previous note object.
          */
         this.cancelEditing = function (note) {
            this.tableElement.attr("contenteditable", "false");
            this.tableElement.html(note.previous);
         };
         
         /**
          * Creates an object that should be filled with data used by updating the note.
          * @method createEmptyObject
          * @return {Object} An object with the following properties: tableid, textid, seqid, and 
          * text.
          */
         this.createEmptyObject = function () {
            return {tableid: this.tableid, textid: this.textid, seqid: this.seqid, text: null};
         };
      }
      /**
       * Defines whether note identifiers are numbers or not.
       * @return {Boolean} 
       */
      Note.prototype.isNaN = function () {
         return isNaN(this.tableid) || (this.tableid !== 0 && isNaN(this.textid)) || isNaN(this.seqid);
      };
      /**
       * Defines whether note identifiers are undefined or not.
       * @return {Boolean} 
       */
      Note.prototype.isUndefined = function () {
         return this.tableid === undefined || this.textid === undefined || this.seqid === undefined;
      };
      /**
       * Defines if note identifiers of this object are equal to another object ones. 
       * @param {Note} note A translation object.
       * @return {Boolean}
       */
      Note.prototype.equals = function (note) {
         return this.tableid === note.tableid && this.textid === note.textid && this.seqid === note.seqid;
      };
      /**
       * Inserts a string at the end of a note.
       * @param {String} content The string to append.
       */
      Note.prototype.append = function (content) {
         pasteTextAtCaret(content);
      };
      // Note string ID that is being edited.
      var previousNote = new Note();
      
      /**
       * Creates and appends a pager to the table. The pager shows the current page and total pages.
       * Four buttons, (begin, previous, next, end), are also available.
       * @method createPager
       * @private
       */
      function createPager() {
         var code = '<div class="ui-widget-header pager" style="padding: 5px; margin: 0px 1px">\n' +
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
         var table = $(tableid);

         // Bind events and show the top and bottom pager.
         if (table.find(".pager").length === 0) {
            table.prepend(code);
            table.append(code);
            table.children(".pager").each(function () {
               $(this).find(".pager-button-first").button({icons: {primary: "ui-icon-seek-first"},
                                                           text: false}).click(pager.onClickFirst);
               $(this).find(".pager-button-prev").button({icons: {primary: "ui-icon-seek-prev"},
                                                          text: false}).click(pager.onClickPrev);
               $(this).find(".pager-button-next").button({icons: {primary: "ui-icon-seek-next"},
                                                          text: false}).click(pager.onClickNext);
               $(this).find(".pager-button-end").button({icons: {primary: "ui-icon-seek-end"},
                                                         text: false}).click(pager.onClickEnd);
               $(this).find(".pager-form").on("submit", {text: $(this).find("input[name=page]")}, pager.onSubmitPage);
               $(this).find(".pager-submit").hide();
               $(this).find(".pager").show();
            });
         }
         table.children(".pager").each(function () {
            $(this).find(".pager-current-page").val(pager.current);
            $(this).find(".pager-total").text(pager.total);
         });
      };
      createPager();

      /**
       * Changes to edit mode when mouse left button is clicked on the target language cell.
       * @method onTranslationClick
       * @private
       * @param {Event} event A jQuery object that selects a <td> element given in data.td.
       */
      function onTranslationClick(event) {
         var current = new Translation(event.data.td);

         // Switch to editing mode if the row is valid.
         if (!current.isNaN() && !previousTranslation.equals(current)) {
            // Close the string that was being edited.
            if (!previousTranslation.isUndefined()) {
               previousTranslation.finishEditing();
            }

            current.startEditing();
            previousTranslation = current;
         }

         event.preventDefault();
      };
      
      /**
       * Filters HTML code when a user pastes data into translation.
       * @method onTranslationPaste
       * @private
       * @param {Event} event A jQuery object that selects a <td> element given in data.td.
       */
      function onTranslationPaste (event) {
          var pastedData = null;

          event.stopPropagation();
          event.preventDefault();
          pastedData = event.originalEvent.clipboardData.getData('text');
          previousTranslation.append(pastedData);
      }

      /**
       * Changes to edit mode when mouse left button is clicked on notes table data.
       * @method onNotesClick
       * @private
       * @param {Event} event A jQuery object that selects a <td> element given in data.td.
       */
      function onNotesClick(event) {
         var current = new Note(event.data.td);

         // Switch to editing mode if the row is valid.
         if (!current.isNaN() && !previousNote.equals(current)) {
            // Close the string that was being edited.
            if (!previousNote.isUndefined()) {
               previousNote.finishEditing();
            }

            current.startEditing();
            previousNote = current;
         }

         event.preventDefault();
      }

      /**
       * Cancels note edition when Esc key is pressed.
       * @method onNotesKeyUp
       * @private
       * @param {Event} event A jQuery object that selects a <td> element given in data.td.
       */
      function onNotesKeyUp(event) {
         if (event.keyCode === 27) { // Esc
            var current = new Note(event.data.td);
            current.cancelEditing(previousNote);
         }
         event.preventDefault();
      }
      
      /**
       * Finishes note edition when table data element loses the focus.
       * @method onNotesFocusOut
       * @private
       * @param {Event} event A jQuery object that selects a <td> element given in data.td.
       */
      function onNotesFocusOut(event) {
         var current = new Note(event.data.td);
         current.finishEditing();
         event.preventDefault();
      }
         
      /**
       * Filters HTML code when a user pastes data into notes.
       * @method onTranslationPaste
       * @private
       * @param {Event} event A jQuery object that selects a <td> element given in data.td.
       */
      function onNotesPaste (event) {
          var pastedData = null;

          event.stopPropagation();
          event.preventDefault();
          pastedData = event.originalEvent.clipboardData.getData('text');
          previousNote.append(pastedData);
      }
      
      /**
       * Binds an ESO code to a dialog that explain what the code is used for.
       * @method bindEsoCode
       * @private
       * @param {jQuery} tableElement A jQuery object that selects a <td> element.
       */
      function bindEsoCode(tableElement) {
         var codeRegex1 = /&lt;&lt;([\s\S]*?)&gt;&gt;/g; // <<1>>, <<C:2>>, <<m:2>>, etc.
         var codeRegex2 = /(\^\w+$)/g; // ^pf, ^m, ^z, etc.
         var html = tableElement.html();
         if (html && codeRegex1.test(html)) {
            html = html.replace(/&lt;&lt;([\s\S]*?)&gt;&gt;/g, '<span class="code-help"><a class="code-help-link" href="#$&">$&</a></span>');
            tableElement.html(html);
            $(".code-help").each(function () {
               app.bindEsoCodeDialog($(this), $("#eso-code-dialog"), $(this).children().html());
            });
         } else if (html && codeRegex2.test(html)) {
            html = html.replace(/(\^\w+$)/g, '<span class="code-help"><a class="code-help-link" href="#$&">$&</a></span>');
            tableElement.html(html);
            $(".code-help").each(function () {
               app.bindEsoCodeDialog($(this), $("#eso-code-dialog"), $(this).children().html());
            });
         }
      }

      // Modify DOM table by means of setting text align and icon, and binding click event with
      // each column header and function sortTable.
      for (var i = 0, max = $(tableid).find("tr:first").children().last().index(); i <= max; i++) {
         row = 0;
         group = 1;
         $(tableid).find("tr").each(function () {
            var tableElement = $(this).children().eq(i);
            tableElement.css("vertical-align", "top");
            // Header
            if (row === 0) {
               // Hide columns.
               if (hidden.indexOf(i) !== -1) {
                  tableElement.hide();
               }
               tableElement.addClass("ui-widget-header");
               if (i === column.notes) {
                  tableElement.css("width", "18%");
               } else if (i === column.string) {
                  tableElement.css("width", "62%");
               }
            }
            // Content
            else {
               if (group % 2 === 0) {
                  tableElement.addClass("odd-" + l10n.misc.theme);
               }
               if (row % 3 === 1) { // First row of each three row group
                  // Hide columns.
                  if (hidden.indexOf(i) !== -1) {
                     tableElement.hide();
                  }
                  if (i === column.type) {
                     var code = parseInt(tableElement.text(), 10);
                     tableElement.empty().css("background", l10n.types[code].color).attr("title", l10n.types[code].tooltip).tooltip();
                  }
                  if (i === column.string) {
                     bindEsoCode(tableElement);
                  }
                  if (i === column.notes) {
                     tableElement.on("click", {td: tableElement.children().first()}, onNotesClick);
                     tableElement.on("keyup", {td: tableElement.children().first()}, onNotesKeyUp);
                     tableElement.on("focusout", {td: tableElement.children().first()}, onNotesFocusOut);
                     tableElement.on("paste", {td: tableElement.children().first()}, onNotesPaste);
                  }
                  if (i === column.status) {
                     var isUpdated = isSpecialTask && tableElement.next().next().text() !== "0";

                     if (tableElement.next().next().next().next().next().next().text() === "1") { // IsDisputed
                        tableElement.addClass("disputed").attr("title", l10n.status.disputed).tooltip();
                     } else if (tableElement.next().next().next().next().next().text() === "1") { // IsLocked
                        tableElement.addClass(isUpdated ? "locked-and-updated" : "locked").attr("title", l10n.status.locked).tooltip();
                     } else if (tableElement.next().next().next().next().text() === "1") { // IsRevised
                        tableElement.addClass(isUpdated ? "revised-and-updated" : "revised").attr("title", l10n.status.revised).tooltip();
                     } else if (tableElement.next().next().next().text() === "1") { // IsTranslated
                        tableElement.addClass(isUpdated ? "not-revised-and-updated" : "not-revised").attr("title", l10n.status.notRevised).tooltip();
                     } else {
                        tableElement.addClass("not-translated").attr("title", l10n.status.notTranslated).tooltip();
                     }
                  }
               } else if (row % 3 === 2) { // Second row of each three row group
                  bindEsoCode(tableElement);
               }
               else if (row % 3 === 0) { // Third row of each three row group
                  if (i === 1) { // Column for translated text
                     tableElement.on("click", {td: tableElement.children().first()}, onTranslationClick);
                     tableElement.on("paste", {td: tableElement.children().first()}, onTranslationPaste);
                  }
                  group++;
               }
               tableElement.addClass("ui-widget-content");
            }
            row++;
         });
      }
   };
})(jQuery);
