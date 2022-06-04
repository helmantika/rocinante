/*
 *  Rocinante. The Elder Scrolls Online Translation Web App.
 *  Copyright (c) 2016 Jorge Rodríguez Santos
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * An object to add any type of module.
 *
 * @type {Rocinante.args|Array}
 */
Rocinante.modules = {};

/**
 * A module that manages the user interface and it is based on jQuery UI capabilities.
 *
 * @param {Rocinante} app A Rocinante object.
 */
Rocinante.modules.ui = function (app) {

   /**
    * Table allows to manipulate tables by means of a pager, two selection modes, and two mouse
    * cursors. Also it returns the data of a selected row.
    *
    * @class Table
    * @constructor
    *
    * @param {jQuery} id A jQuery object that selects a <table> element.
    * @param {Integer} rpp Number of rows per page to show. Set this argument to 0 to not show the
    * pager.
    * @param {Function} request A function that implements how to request data to the table. The
    * function must have one argument: a jQuery object that selects a <table> element.
    */
   function Table(id, rpp, request) {
      var tableId = id;
      var rowsPerPage = rpp || 0;
      var request = request;
      var that = this;

      /**
       * Defines how to sort the table when it has a pager and there are more pages than one.
       * @property sorting
       * @type {Object}
       * @private
       */
      var sorting = {
         /**
          * Index of the column that will be used to sort the table.
          * @property index
          * @type {Integer}
          */
         index: -1,
         /**
          * Defines whether sorting is ascending or not.
          * @property asc
          * @type {Boolean}
          */
         asc: false
      };
      
      /**
       * Defines how to render a selected row, and what to do when a row is selected or unselected.
       * @property selection
       * @type {Object}
       * @private
       */
      var selection = {
         /**
          * When a row is selected, stores its content by splitting it up. Each array position
          * stores data from a column of the row. The left-most column is stored at first array
          * position (zero).
          * @property row
          * @type {Array}
          */
         row: [],
         /**
          * Defines whether a selected row is rendered with a different color/pattern.
          * @property mark
          * @type {Boolean}
          * @default false
          */
         mark: false,
         /**
          * Defines whether a hand cursor is shown or not when mouse is over the table.
          * @property hand
          * @type {Boolean}
          * @default false
          */
         hand: false,
         /**
          * What to do when a row is selected.
          * @method onSelect
          */
         onSelect: function () {},
         /**
          * What to do when a row is unselected.
          * @method onUnselect
          */
         onUnselect: function () {},
         /**
          * Sets what to do when the table is clear.
          * @method onClear
          * @param {Function} callback A function with no parameters.
          */
         onClear: function(callback) {
            this.clear = callback;
         },
         /**
          * Clear the selected row, if there is any.
          * @method clear
          */
         clear: function() {}
      };

      /**
       * Allows the user to show table pages when the table is paginated.
       * @property pager
       * @type {Object}
       * @private
       */
      var pager = {
         /**
          * The current page number.
          * @property current
          * @type {Integer}
          */
         current: 0,
         /**
          * The total number of pages.
          * @property total
          * @type {Integer}
          */
         total: 0,
         /**
          * Shows the first page.
          * @method onClickFirst
          * @param {Event} e
          */
         onClickFirst: function (e) {
            if (that.firstPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows the previous page.
          * @method onClickPrev
          * @param {Event} e
          */
         onClickPrev: function (e) {
            if (that.decPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows the next page.
          * @method onClickNext
          * @param {Event} e
          */
         onClickNext: function (e) {
            if (that.incPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows the last page.
          * @method onClickEnd
          * @param {Event} e
          */
         onClickEnd: function (e) {
            if (that.lastPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows a specific page.
          * @method onSubmitPage
          * @param {Event} e Data has a jQuery object that select an element whose value stores the
          * page number.
          */
         onSubmitPage: function (e) {
            if (that.goToPage(e.data.text.val())) {
               that.request();
            }
            e.preventDefault();
         }
      };

      /**
       * Stores column indices that will not be shown. Zero is the left-most column index.
       * @property hidden
       * @type {Array}
       * @private
       */
      var hidden = [];

      /**
       * Gets the object that defines how to sort the table when it has a pager and there are more 
       * pages than one.
       * @method getSorting
       * @return {Object} The sorting property.
       */
      this.getSorting = function () {
         return sorting;
      };
      
      /**
       * Gets the selection object used by rtable.
       * @method getSelection
       * @return {Object} The selection property.
       */
      this.getSelection = function () {
         return selection;
      };

      /**
       * Gets the pager object used by rtable.
       * @method getPager
       * @return {Object} The pager property.
       */
      this.getPager = function () {
         return pager;
      };     
      
      /**
       * Sets how to sort the table when it has a pager and there are more pages than one.
       * @param {Integer} index Index of the column that will be used to sort the table.
       */
      this.setSorting = function(index) {
         sorting.index = index;
         sorting.asc = !sorting.asc;
      };
      
      /**
       * Sets whether a selected row is rendered with a different color/pattern or not.
       * @method setMarked
       * @param {Boolean} marked true to mark a selected row, otherwise false.
       */
      this.setMarked = function(marked) {
         selection.mark = marked;
      };

      /**
       * Sets whether a hand cursor is shown or not when mouse is over the table.
       * @method setMarked
       * @param {Boolean} pointer true to show a hand cursor, false to show the standard one.
       */
      this.setPointer = function(pointer) {
         selection.hand = pointer;
      };

      /**
       * Sets what to do when a row is selected.
       * @method onSelect
       * @param {Function} callback A function with no parameters.
       */
      this.onSelect = function(callback) {
         selection.onSelect = callback;
      };

      /**
       * Sets what to do when a selected row is unselected.
       * @method onUnselect
       * @param {Function} callback A function with no parameters.
       */
      this.onUnselect = function(callback) {
         selection.onUnselect = callback;
      };
      
      /**
       * Calls the callback that was set in the constructor. Table data will be requested to the
       * server.
       * @method request
       */
      this.request = function() {
         request(tableId);
      };
      
      /**
       * Sets how many rows of the table are shown. If zero is set, the pager will be hide and
       * request callback will retrieve all the rows to the server.
       *
       * @method setRowsPerPage
       * @param {Integer} rpp A number of rows, or 0 to hide the pager and to show the whole table.
       */
      this.setRowsPerPage = function(rpp) {
         rowsPerPage = rpp;
      };

      /**
       * Gets how many rows of the table are shown. If zero is returned, the pager is hidden and
       * request callback retrieves all the rows to the server.
       *
       * @method getRowsPerPage
       * @return {Integer} A number of rows, or 0 if the pager is hidden.
       */
      this.getRowsPerPage = function () {
         return rowsPerPage;
      };

      /**
       * Sets the current page number. The first page is 1.
       *
       * @method setPage
       * @param {Integer} page A number greater than 0.
       */
      this.setPage = function(page) {
         pager.current = page;
      };

      /**
       * Gets the current page number. The first page is 1.
       * @method getPage
       * @return {Integer} page A number greater than 0.
       */
      this.getPage = function () {
         return pager.current;
      };

      /**
       * Sets the number of pages that table has. This method should be invoked by request callback.
       *
       * @method setTotalPages
       * @param {Integer} pages A number greater than 0.
       */
      this.setTotalPages = function(pages) {
         pager.total = pages;
      };

      /**
       * Sets contents of the selected row. This method only must be used when the selection
       * mode of the table is disabled.
       *
       * @method setSelectedRow
       * @param {Array} data Each array element stores data from a column. The left-most column is
       * stored at first array position (zero).
       */
      this.setSelectedRow = function (data) {
         selection.row = data;
      };

      /**
       * Gets content of table element given by the selected row and a column number.
       *
       * @method getSelectedRow
       * @param {Integer} column The first column starts at 0.
       * @return {String} Content of a table element.
       */
      this.getSelectedRow = function (column) {
         return selection.row !== undefined ? selection.row[column] : undefined;
      };

      /**
       * Sets which table columns will be hidden.
       *
       * @method setHiddenColumns
       * @param {Array} data An array with zero-based indices.
       */
      this.setHiddenColumns = function (data) {
         hidden = data;
      };

      /**
       * Gets which table columns will be hidden.
       * @return {Array} An array with zero-based indices.
       */
      this.getHiddenColumns = function () {
         return hidden;
      };

      /**
       * Sets the current page to 1.
       *
       * @method firstPage
       * @return {Boolean} Whether the current page is different from the first one or not.
       */
      this.firstPage = function () {
         var changed = false;
         if (pager.current !== 1) {
            pager.current = 1;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to the previous one.
       *
       * @method decPage
       * @return {Boolean} Whether the current page is different from the previous one or not.
       */
      this.decPage = function () {
         var changed = false;
         if (pager.current > 1) {
            pager.current--;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to the next one.
       *
       * @method incPage
       * @return {Boolean} Whether the current page is different from the next one or not.
       */
      this.incPage = function () {
         var changed = false;
         if (pager.current < pager.total) {
            pager.current++;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to the last one.
       *
       * @method lastPage
       *
       * @return {Boolean} Whether the current page is different from the last one or not.
       */
      this.lastPage = function () {
         var changed = false;
         if (pager.current !== pager.total) {
            pager.current = pager.total;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to a specific one.
       *
       * @method goToPage
       * @param {Integer} page A page number. A number greater than 0.
       * @return {Boolean} Whether the current page is different from the specified one or not.
       */
      this.goToPage = function (page) {
         var changed = false;
         if (page >= 1 && page <= pager.total) {
            pager.current = page;
            changed = true;
         }
         return changed;
      };
   };
   
   /**
    * A table for user management.
    *
    * @attribute userTable
    * @type Table
    */
   app.userTable = new Table($("#user-table"), 25, function () {
      var sorting = app.userTable.getSorting();
      app.listUsers($("#user-table"), sorting.index, sorting.asc);
   });
   app.userTable.setMarked(true);
   app.userTable.onSelect(function () {
      $("#modify-user-button").show();
      $("#delete-user-button").show();
   });
   app.userTable.onUnselect(function () {
      $("#modify-user-button").hide();
      $("#delete-user-button").hide();
   });
   app.userTable.getSelectedUsername = function() {
      return app.userTable.getSelectedRow(0);
   };

   /**
    * A table that lists tasks assigned to the current user.
    *
    * @attribute userTaskTable
    * @type Table
    */
   app.userTaskTable = new Table($("#user-task-table"), 10, function () {
      var sorting = app.userTaskTable.getSorting();
      app.listUserTasks($("#user-task-table"), sorting.index, sorting.asc);
   });
   app.userTaskTable.setMarked(true);
   app.userTaskTable.onSelect(function () {
      app.assignerTaskTable.getSelection().clear();
      $("#open-task-button").show();
      $("#reassign-task-button").hide();
      $("#revise-task-button").hide();
      $("#delete-task-button").show();
   });
   app.userTaskTable.onUnselect(function () {
      $("#open-task-button").hide();
      $("#reassign-task-button").hide();
      $("#revise-task-button").hide();
      $("#delete-task-button").hide();
   });
   app.userTaskTable.getSelectedTaskId = function() {
      var row = app.userTaskTable.getSelectedRow(0);
      return row !== undefined ? row.replace(/([,|\.])+/, "") : undefined;
   };
   app.userTaskTable.getSelectedTaskType = function() {
      return app.userTaskTable.getSelectedRow(2);
   };
   app.userTaskTable.getSelectedTaskProgress = function() {
      return app.userTaskTable.getSelectedRow(5);
   };
   app.userTaskTable.removeSelectedTaskId = function() {
      return app.userTaskTable.setSelectedRow(undefined);
   };
   
   /**
    * A table that lists tasks assigned by the current user.
    *
    * @attribute assignerTaskTable
    * @type Table
    */
   app.assignerTaskTable = new Table($("#assigner-task-table"), 10, function () {
      var sorting = app.assignerTaskTable.getSorting();
      app.listAssignerTasks($("#assigner-task-table"), sorting.index, sorting.asc);
   });
   app.assignerTaskTable.setMarked(true);
   app.assignerTaskTable.onSelect(function () {
      app.userTaskTable.getSelection().clear();
      $("#open-task-button").show();
      $("#reassign-task-button").show();
      if (app.getLocalization().getTaskType(app.assignerTaskTable.getSelectedTaskType()) === "TRANSLATION") {
         $("#revise-task-button").show();
      } else {
         $("#revise-task-button").hide();
      }
      $("#delete-task-button").show();
   });
   app.assignerTaskTable.onUnselect(function () {
      $("#open-task-button").hide();
      $("#reassign-task-button").hide();
      $("#revise-task-button").hide();
      $("#delete-task-button").hide();
   });
   app.assignerTaskTable.getSelectedTaskId = function() {
      var row = app.assignerTaskTable.getSelectedRow(0);
      return row !== undefined ? row.replace(/([,|\.])+/, "") : undefined;
   };
   app.assignerTaskTable.getSelectedTaskType = function() {
      return app.assignerTaskTable.getSelectedRow(2);
   };
   app.assignerTaskTable.removeSelectedTaskId = function() {
      return app.assignerTaskTable.setSelectedRow(undefined);
   };

   /**
    * A table that lists every task.
    *
    * @attribute adminTaskTable
    * @type Table
    */
   app.adminTaskTable = new Table($("#admin-task-table"), 25, function () {
      var sorting = app.adminTaskTable.getSorting();
      app.listTasks($("#admin-task-table"), sorting.index, sorting.asc);
   });
   app.adminTaskTable.setMarked(true);
   app.adminTaskTable.onSelect(function () {
      $("#open-task-admin-button").show();
      $("#reassign-task-admin-button").show();
      if (app.getLocalization().getTaskType(app.adminTaskTable.getSelectedTaskType()) === "TRANSLATION") {
         $("#revise-task-admin-button").show();
      } else {
         $("#revise-task-admin-button").hide();
      }
      $("#delete-task-admin-button").show();
   });
   app.adminTaskTable.onUnselect(function () {
      $("#open-task-admin-button").hide();
      $("#reassign-task-admin-button").hide();
      $("#revise-task-admin-button").hide();
      $("#delete-task-admin-button").hide();
   });
   app.adminTaskTable.getSelectedTaskId = function() {
      var row = app.adminTaskTable.getSelectedRow(0);
      return row !== undefined ? row.replace(/([,|\.])+/, "") : undefined;
   };
   app.adminTaskTable.getSelectedTaskType = function() {
      return app.adminTaskTable.getSelectedRow(2);
   };
   app.adminTaskTable.removeSelectedTaskId = function() {
      return app.adminTaskTable.setSelectedRow(undefined);
   };
   
   /**
    * A table that lists The Elder Scrolls Online language tables. This table only shows two 
    * fields. It's used for selection.
    *
    * @attribute briefEsoIndexTable
    * @type Table
    */
   app.briefEsoIndexTable = new Table($("#brief-esotable"), 0, function () {
      app.listBriefEsoTable($("#brief-esotable"));
   });
   app.briefEsoIndexTable.setMarked(true);
   app.briefEsoIndexTable.setHiddenColumns([0]); // 0 = TableId
   app.briefEsoIndexTable.onSelect(function () {
      $("#select-esotable-dialog").parent().find("#button-ok").attr("disabled", false);
   });
   app.briefEsoIndexTable.onUnselect(function () {
      $("#select-esotable-dialog").parent().find("#button-ok").attr("disabled", true);
   });
   app.briefEsoIndexTable.getSelectedTableId = function () {
      return app.briefEsoIndexTable.getSelectedRow(0);
   };
   app.briefEsoIndexTable.getSelectedTableNumber = function () {
      return app.briefEsoIndexTable.getSelectedRow(1);
   };
   
   /**
    * A table that lists The Elder Scrolls Online language tables.
    *
    * @attribute esoIndexTable
    * @type Table
    */
   app.esoIndexTable = new Table($("#main-table"), 0, function () {
      app.listEsoTables($("#main-table"));
   });
   app.esoIndexTable.setPointer(true);
   app.esoIndexTable.setHiddenColumns([0]); // TableId
   app.esoIndexTable.onSelect(function () {
      var tableName = app.esoIndexTable.getSelectedTablename();
      var tabid = app.addTab($("#tab-list"), tableName, tableName, true);
      $("#tab-list").parent().tabs("option", "active", -1);
      app.createLangTable(tabid, app.esoIndexTable.getSelectedTableId());
   });
   app.esoIndexTable.setSelectedTable = function (data) {
      app.esoIndexTable.setSelectedRow(data);
   };
   app.esoIndexTable.getSelectedTableId = function () {
      return app.esoIndexTable.getSelectedRow(0);
   };
   app.esoIndexTable.getSelectedTablename = function () {
      return app.esoIndexTable.getSelectedRow(1);
   };
   app.esoIndexTable.getSelectedDescription = function () {
      return app.esoIndexTable.getSelectedRow(2);
   };

   /**
    * A table that list Rocinante's users. This table only shows usernames. It's used for 
    * selection.
    *
    * @attribute briefUserTable1
    * @type Table
    */
   app.briefUserTable1 = new Table($("#brief-usertable-1"), 0, function () {
      app.listBriefUserTable($("#brief-usertable-1"), app.briefUserTable1);
   });
   app.briefUserTable1.setMarked(true);
   app.briefUserTable1.onSelect(function () {
      $("#select-user-dialog-1").parent().find("#button-ok").attr("disabled", false);
   });
   app.briefUserTable1.onUnselect(function () {
      $("#select-user-dialog-1").parent().find("#button-ok").attr("disabled", true);
   });
   app.briefUserTable1.getSelectedUsername = function () {
      return app.briefUserTable1.getSelectedRow(0);
   };
   
   /**
    * A table with a list of Rocinante's users. This table only shows usernames. It's used for 
    * selection.
    *
    * @attribute briefUserTable2
    * @type Table
    */
   app.briefUserTable2 = new Table($("#brief-usertable-2"), 0, function () {
      app.listBriefUserTable($("#brief-usertable-2"), app.briefUserTable2);
   });
   app.briefUserTable2.setMarked(true);
   app.briefUserTable2.onSelect(function () {
      $("#select-user-dialog-2").parent().find("#button-ok").attr("disabled", false);
   });
   app.briefUserTable2.onUnselect(function () {
      $("#select-user-dialog-2").parent().find("#button-ok").attr("disabled", true);
   });
   app.briefUserTable2.getSelectedUsername = function () {
      return app.briefUserTable2.getSelectedRow(0);
   };
   
   /**
    * A table with a list of Rocinante's users. This table only shows usernames. It's used for 
    * add more addreesses to a message that was a draft.
    *
    * @attribute briefUserTable3
    * @type Table
    */
   app.briefUserTable3 = new Table($("#brief-usertable-3"), 0, function () {
      app.listBriefUserTable($("#brief-usertable-3"), app.briefUserTable3);
   });
   app.briefUserTable3.setMarked(true);
   app.briefUserTable3.onSelect(function () {
      $("#select-user-dialog-3").parent().find("#button-ok").attr("disabled", false);
   });
   app.briefUserTable3.onUnselect(function () {
      $("#select-user-dialog-3").parent().find("#button-ok").attr("disabled", true);
   });
   app.briefUserTable3.getSelectedUsername = function () {
      return app.briefUserTable3.getSelectedRow(0);
   };
   
   /**
    * A table that shows user statistics.
    *
    * @attribute statsTable
    * @type Table
    */
   app.statsTable = new Table($("#stats-table"), 25, function () {
      var sorting = app.statsTable.getSorting();
      app.listStats($("#stats-table"), sorting.index, sorting.asc);
   });

   /**
    * The Elder Scrolls Online language tables.
    *
    * @attribute langTables
    * @type Array
    */
   app.langTables = {};

   /**
    * Creates and requests a language table.
    *
    * @method createLangTable
    * @param {Object} id A jQuery selector.
    * @param {String} tableid Table ID.
    */
   app.createLangTable = function (id, tableid) {
      var table = new Table(id, 50, function () {
         app.listLangTable(id, tableid);
      });
      app.langTables[id] = table;
      app.langTables[id].request();
   };

   /**
    * Task tables.
    *
    * @attribute taskTables
    * @type Array
    */
   app.taskTables = {};
   
   /**
    * Creates and requests a task table.
    *
    * @method createTaskTable
    * @param {Object} id A jQuery selector.
    * @param {String} taskid A task ID.
    */
   app.createTaskTable = function (id, taskid) {
      var table = new Table(id, 50, function () {
         app.listTaskTable(id, taskid);
      });
      app.taskTables[id] = table;
      app.taskTables[id].request();
   };
   
   /**
    * Search table for lang.
    *
    * @attribute searchLangTable
    * @type Object
    */
   app.searchLangTable = {};
   
   /**
    * Search table for lua.
    *
    * @attribute searchLuaTable
    * @type Object
    */
   app.searchLuaTable = {};
   
   /**
    * The string that is being searched.
    * 
    * @attribute searchText
    * @type String
    */
   app.searchText = null;
   
   /**
    * Creates and requests a table for searching in Lang.
    *
    * @method createSearchLangTable
    * @param {Object} id A jQuery selector.
    */
   app.createSearchLangTable = function (id) {
      app.searchText = $("input[name=search-text]").val();
      var table = new Table(id, 50, function () {
         app.search(id, app.searchText, "Lang");
      });
      
      app.searchLangTable = table;
      app.searchLangTable.request();
   };
   
   /**
    * Creates and requests a table for searching in Lua.
    *
    * @method createSearchLuaTable
    * @param {Object} id A jQuery selector.
    */
   app.createSearchLuaTable = function (id) {
      app.searchText = $("input[name=search-text]").val();
      var table = new Table(id, 50, function () {
         app.search(id, app.searchText, "Lua");
      });
      
      app.searchLuaTable = table;
      app.searchLuaTable.request();
   };
   
   /**
    * Accordion allows manipulate accordions by means of a pager.
    *
    * @class Accordion
    * @constructor
    *
    * @param {jQuery} id A jQuery object that selects a <div> element.
    * @param {Integer} rpp Number of rows per page to show. Set this argument to 0 to not show the
    * pager.
    * @param {Function} request A function that implements how to request data for the table. The
    * function must have one argument: a jQuery object that selects a <table> element.
    */
   function Accordion(id, rpp, request) {
      var divid = id;
      var rowsPerPage = rpp || 0;
      var request = request;
      var that = this;
      
      /**
       * Stores data of the selected element.
       * @type object
       */
      var selection = {
         /**
          * When an item is selected, stores its content.
          * @property object
          * @type {Object}
          */
         object: {},
         /**
          * What to do when a row is selected.
          * @method onSelect
          */
         onSelect: function () {},
         /**
          * What to do when a row is unselected.
          * @method onUnselect
          */
         onUnselect: function () {}
      };
      
      /**
       * Allows the user to show accordion pages when the accordion is paginated.
       * @property pager
       * @type {Object}
       * @private
       */
      var pager = {
         /**
          * The current page number.
          * @property current
          * @type {Integer}
          */
         current: 0,
         /**
          * The total number of pages.
          * @property total
          * @type {Integer}
          */
         total: 0,
         /**
          * Shows the first page.
          * @method onClickFirst
          * @param {Event} e
          */
         onClickFirst: function (e) {
            if (that.firstPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows the previous page.
          * @method onClickPrev
          * @param {Event} e
          */
         onClickPrev: function (e) {
            if (that.decPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows the next page.
          * @method onClickNext
          * @param {Event} e
          */
         onClickNext: function (e) {
            if (that.incPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows the last page.
          * @method onClickEnd
          * @param {Event} e
          */
         onClickEnd: function (e) {
            if (that.lastPage()) {
               that.request();
            }
            e.preventDefault();
         },
         /**
          * Shows a specific page.
          * @method onSubmitPage
          * @param {Event} e Data has a jQuery object that select an element whose value stores the
          * page number.
          */
         onSubmitPage: function (e) {
            if (that.goToPage(e.data.text.val())) {
               that.request();
            }
            e.preventDefault();
         }
      };

      /**
       * Gets the selected element.
       * @method getSelection
       * @return {Object} The selection property.
       */
      this.getSelection = function () {
         return selection;
      };
      
      /**
       * Gets the pager object used by raccordion.
       * @method getPager
       * @return {Object} The pager property.
       */
      this.getPager = function () {
         return pager;
      };
      
      /**
       * Sets what to do when an item is selected.
       * @method onSelect
       * @param {Function} callback A function with no parameters.
       */
      this.onSelect = function(callback) {
         selection.onSelect = callback;
      };

      /**
       * Sets what to do when a selected item is unselected.
       * @method onUnselect
       * @param {Function} callback A function with no parameters.
       */
      this.onUnselect = function(callback) {
         selection.onUnselect = callback;
      };
      
      /**
       * Calls the callback that was set in the constructor. Table data will be requested to the
       * server.
       * @method request
       */
      this.request = function() {
         request(divid);
      };
      
      /**
       * Sets how many rows of the table are shown. If zero is set, the pager will be hide and
       * request callback will retrieve all the rows to the server.
       *
       * @method setRowsPerPage
       * @param {Integer} rpp A number of rows, or 0 to hide the pager and to show the whole 
       * accordion.
       */
      this.setRowsPerPage = function(rpp) {
         rowsPerPage = rpp;
      };

      /**
       * Gets how many rows of the raccordion are shown. If zero is returned, the pager is hidden 
       * and request callback retrieves all the messages to the server.
       *
       * @method getRowsPerPage
       * @return {Integer} A number of rows, or 0 if the pager is hidden.
       */
      this.getRowsPerPage = function () {
         return rowsPerPage;
      };

      /**
       * Sets the current page number. The first page is 1.
       *
       * @method setPage
       * @param {Integer} page A number greater than 0.
       */
      this.setPage = function(page) {
         pager.current = page;
      };

      /**
       * Gets the current page number. The first page is 1.
       * @method getPage
       * @return {Integer} page A number greater than 0.
       */
      this.getPage = function () {
         return pager.current;
      };

      /**
       * Sets the number of pages that accordion has. This method should be invoked by request callback.
       *
       * @method setTotalPages
       * @param {Integer} pages A number greater than 0.
       */
      this.setTotalPages = function(pages) {
         pager.total = pages;
      };

      /**
       * Sets the current page to 1.
       *
       * @method firstPage
       * @return {Boolean} Whether the current page is different from the first one, or not.
       */
      this.firstPage = function () {
         var changed = false;
         if (pager.current !== 1) {
            pager.current = 1;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to the previous one.
       *
       * @method decPage
       * @return {Boolean} Whether the current page is different from the previous one, or not.
       */
      this.decPage = function () {
         var changed = false;
         if (pager.current > 1) {
            pager.current--;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to the next one.
       *
       * @method incPage
       * @return {Boolean} Whether the current page is different from the next one, or not.
       */
      this.incPage = function () {
         var changed = false;
         if (pager.current < pager.total) {
            pager.current++;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to the last one.
       *
       * @method lastPage
       *
       * @return {Boolean} Whether the current page is different from the last one, or not.
       */
      this.lastPage = function () {
         var changed = false;
         if (pager.current !== pager.total) {
            pager.current = pager.total;
            changed = true;
         }
         return changed;
      };

      /**
       * Sets the current page to a specific one.
       *
       * @method goToPage
       * @param {Integer} page A page number.
       * @return {Boolean} Whether the current page is different from the specified one, or not.
       */
      this.goToPage = function (page) {
         var changed = false;
         if (page >= 1 && page <= pager.total) {
            pager.current = page;
            changed = true;
         }
         return changed;
      };
   };
   
   /**
    * An accordion that shows messages that were sent by the current user.
    *
    * @attribute outboxAccordion
    * @type Accordion
    */
   app.outboxAccordion = new Accordion($("#mail-outbox"), 20, function () {
      app.requestOutboxMessages($("#mail-outbox"));
   });
   app.outboxAccordion.onSelect(function () {
      $("#new-reply-button").hide();
      $("#new-reply-all-button").hide();
      $("#open-draft-button").hide();
      $("#delete-message-button").show();
   });
   app.outboxAccordion.onUnselect(function () {
      $("#new-reply-button").hide();
      $("#new-reply-all-button").hide();
      $("#open-draft-button").hide();
      $("#delete-message-button").hide();
   });
      
   /**
    * An accordion that shows messages that were sent to the current user.
    *
    * @attribute inboxAccordion
    * @type Accordion
    */
   app.inboxAccordion = new Accordion($("#mail-inbox"), 20, function () {
      app.requestInboxMessages($("#mail-inbox"));
   });
   app.inboxAccordion.onSelect(function () {
      $("#new-reply-button").show();
      $("#new-reply-all-button").show();
      $("#open-draft-button").hide();
      $("#delete-message-button").show();
      if (this.object.isRead === "0") {
         app.markAsRead(this.object.mailid);
         this.object.selector.eq(0).html(this.object.selector.eq(0).first("strong").children().first().html());
      }
      
   });
   app.inboxAccordion.onUnselect(function () {
      $("#new-reply-button").hide();
      $("#new-reply-all-button").hide();
      $("#open-draft-button").hide();
      $("#delete-message-button").hide();
   });
   
   /**
    * An accordion that shows drafts that were saved by the current user.
    *
    * @attribute draftsAccordion
    * @type Accordion
    */
   app.draftsAccordion = new Accordion($("#mail-drafts"), 20, function () {
      app.requestDrafts($("#mail-drafts"));
   });
   app.draftsAccordion.onSelect(function () {
      $("#new-reply-button").hide();
      $("#new-reply-all-button").hide();
      $("#open-draft-button").show();
      $("#delete-message-button").show();
   });
   app.draftsAccordion.onUnselect(function () {
      $("#new-reply-button").hide();
      $("#new-reply-all-button").hide();
      $("#open-draft-button").hide();
      $("#delete-message-button").hide();
   });
   
   /**
    * Creates tabs for a <ul> that contains an unordered list. Every <li> element will become a tab.
    *
    * @method createTabs
    * @param {Object} divid A jQuery object that selects a <div> element that contains a <ul>.
    */
   app.createTabs = function (divid) {
      divid.tabs();
   };

   /**
    * Adds a new tab to a <ul> container.
    * @param {jQuery} ulid A jQuery object that selects a <ul> element.
    * @param {String} tabid A identifier for the new tab.
    * @param {String} caption A caption for the new tab.
    * @param {Boolean} isClosable Whether or not the new tab can be closed.
    * @param {Boolean} isUnique Whether or not the new tab is the only one of some kind.
    * @return {String} ID assigned to the new tab.
    */
   app.addTab = function (ulid, tabid, caption, isClosable, isUnique) {
      var html = null;
      var now = new Date().getTime();
      isClosable = isClosable || false;
      isUnique = isUnique || false;
      tabid = (tabid || caption);
      if (!isUnique) {
         tabid += '-' + now; // Create an univocal ID for this non unique tab.
      }
      
      // Tab
      html = '<li><a href="#' + tabid + '">' + caption + '</a><span class="ui-icon';
      if (isClosable) {
         html += ' ui-icon-circle-close ui-closable-tab';
      }
      html += '" style="display: table;"></span></li>';
      ulid.append(html);

      // Div for tab
      html = '<div id="' + tabid + '">\n</div>';
      ulid.parent().append(html);

      app.refreshTabs(ulid.parent());

      return tabid;
   };

   /**
    * Process any tabs that were added or removed directly in the DOM and recompute the height of
    * the tab panels.
    *
    * @method refreshTabs
    * @param {jQuery} divid A jQuery object that selects a <div> element that contains a <ul>.
    */
   app.refreshTabs = function (divid) {
      divid.tabs("refresh");
      $(".ui-closable-tab").on("click", function () {
         var href = $(this).prev().attr("href");
         $(this).parent().remove();
         $(href).remove();
         delete app.langTables[href.substring(1)];
         divid.tabs("option", "active", 0);
         divid.tabs("refresh");
      });
   };

   /**
    * Creates a non-active and collapsible accordion for a <div> that contains pair of <div>s.
    *
    * @method createAccordion
    * @param {Object} divid A jQuery object that selects a <div> element that contains a <div>s.
    */
   app.createAccordion = function (divid) {
      divid.accordion({
         heightStyle: "content",
         collapsible: true,
         active: false
      });
   };
   
   /**
    * Decorates a <button> element.
    *
    * @method createButton
    * @param {jQuery} buttonid A jQuery object that selects a <button> element.
    * @param {String} icon A jQuery UI icon.
    */
   app.createButton = function (buttonid, icon) {
      buttonid.button({
         icons: {primary: icon},
         text: true
      });
   };

   /**
    * Creates a combo box for a <select> element. Every <option> element will become an entry.
    *
    * @method createComboBox
    * @param {jQuery} menuid A jQuery object that selects a <select> element.
    * @param {Number} width The combo box width given in pixels. It must be equal than
    * (rocinante.css .content input).
    * @param {Number} height Max height of the combo when it is open. Sets 0 to not limit its height.
    * @param {Function} onChange The callback that receives the selected item.
    */
   app.createComboBox = function (menuid, width, height, onChange) {
      width = width || 180;
      height = height || 0;
      var combo = menuid.selectmenu({width: width, change: onChange});
      if (height !== 0) {
         combo.selectmenu("menuWidget").css("height", height + "px");
      }
   };

   /**
    * Creates a jQuery dialog.
    *
    * @method createDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Integer} width The dialog width given in pixels.
    * @param {Function} onOk A callback that will be invoked when the user clicks on 'OK' button.
    * @param {Function} onCancel A callback that will be invoked when the user clicks on 'Cancel' button.
    */
   app.createDialog = function (dialogid, width, onOk, onCancel) {
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getOkCaption(), click: onOk},
            {text: app.getLocalization().getCancelCaption(), click: onCancel}
         ]
      });
   };

   /**
    * Hides all widgets that have to be hidden at start.
    *
    * @method hideWidgets
    */
   app.hideWidgets = function () {
      $("#maintenance-mode-status").hide();
      $("#important-info").hide();
      
      $("#eso-table-info").hide();
      $("#admin-users-info").hide();
      $("#user-task-info").hide();
      $("#admin-task-info").hide();
      $("#user-mail-info").hide();
      $("#admin-addon-info").hide();
      $("#browse-extra-files-row").hide();
      $("#creating-addon").hide();
      $("#browse-update-files-row-1").hide();
      $("#browse-update-files-row-2").hide();
      $("#browse-update-files-row-3").hide();
      $("#updating-database").hide();
      $("#admin-update-info").hide();
      $("#current-user-info").hide();
      
      // Mail buttons.
      $("#new-reply-button").hide();
      $("#new-reply-all-button").hide();
      $("#open-draft-button").hide();
      $("#delete-message-button").hide();
   };

   /**
    * Whether translator can create updating task or not.
    *
    * @attribute areUpdatingTasksAvailable
    * @type Boolean
    */
   app.areUpdatingTasksAvailable = false;
   
   /**
    * Shows a banner that informs about something important after making a management operation.
    * The banner is hidden after 8 seconds.
    *
    * @method showBanner
    * @param {jQuery} id A jQuery object that selects a <div> element.
    * @param {String} message Banner content.
    */
   app.showBanner = function (id, message) {
      id.find("strong").html(message);
      id.slideDown();
      setTimeout(function () {
         id.slideUp();
      }, 8000);
   };
   
   /**
    * Shows a translation tip randomly.
    * 
    * @method showTip
    */
   app.showTip = function () {
      var tips = app.getLocalization().getTips();
      var size = tips.length;
      $("#motd").html(tips[Math.floor(Math.random() * size)]);
   };
   
   /**
    * Updates status and tables every 10 minutes.
    * @see Analizar qué datos es interesante pedir al servidor cada 10 minutos. Si alguno requiere
    * restaurar la sesión, eliminar app.refreshSession() y la clase RefreshSession del servidor.
    */
   app.refresh = function () {
      setInterval(function () {
         app.refreshSession();
         app.checkMaintenanceMode();
         app.requestTranslationStatus($("#translation-status"));
         app.requestPendingTasks($("#task-button"));
         app.requestNewMessages($("#mail-button"));
         app.requestAddonFileInfo();
      }, 600000);
   };
};

/**
 * A module that creates jQuery dialogs used by the application.
 *
 * @param {Rocinante} app A Rocinante object.
 */
Rocinante.modules.dialog = function (app) {

   /**
    * Creates a dialog to modify an ESO table description.
    *
    * @method createUpdateEsoTableDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Integer} width The dialog width given in pixels.
    */
   app.createUpdateEsoTableDialog = function (dialogid, width) {
      var selectedTableType = 0;

      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getOkCaption(),
               click: function () {
                  app.updateEsoTable($(this));
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "translation/SelectEsoTable", tableid: app.esoIndexTable.getSelectedTableId()},
               success: function (data) {
                  var tableTypeSelectmenu = dialogid.find("#select-table-type");

                  $(dialogid).find("input[name=tablename]").val(app.esoIndexTable.getSelectedTablename());
                  $(dialogid).find("input[name=description]").val(data.description);

                  tableTypeSelectmenu.selectmenu("destroy");
                  tableTypeSelectmenu.empty();

                  for (var i = 0, max = data.types.length; i < max; i++) {
                     tableTypeSelectmenu.append($("<option>").attr("value", i).text(data.types[i]));
                  }
                  selectedTableType = parseInt(data.type, 10);
                  tableTypeSelectmenu.val(selectedTableType);
                  app.createComboBox(dialogid.find("#select-table-type"), 300, 100, function (event, ui) {
                     selectedTableType = ui.item.value;
                  });
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });

      app.createComboBox(dialogid.find("#select-table-type"), 300, 100, function (event, ui) {
         selectedTableType = ui.item.value;
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property modifyUserDialog
       * @type {Object}
       */
      app.modifyEsoTableDialog = {
         /**
          * Returns type of the selected table.
          * @method getSelectedType
          * @return {String} A localized string.
          */
         getSelectedType: function () {
            return selectedTableType;
         }
      };
   };

   /**
    * Creates a dialog to create a new user account.
    *
    * @method createNewUserDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createNewUserDialog = function (dialogid, width) {
      var selectedUserType = "TRANSLATOR";
      var selectedUserGender = "MALE";
      var selectedAdvisorName = 0;

      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getOkCaption(),
               click: function () {
                  app.insertUser($(this));
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            var userTypeSelectmenu = dialogid.find("#select-user-type");
            var userGenderSelectmenu = dialogid.find("#select-user-gender");
            var advisorNameSelectmenu = dialogid.find("#select-advisor-name");

            // Sets the dialog to its initial state.
            selectedUserType = "TRANSLATOR";
            userTypeSelectmenu.val(selectedUserType);
            userTypeSelectmenu.selectmenu("refresh");
            selectedUserGender = "MALE";
            userGenderSelectmenu.val(selectedUserGender);
            userGenderSelectmenu.selectmenu("refresh");

            dialogid.find("input").val("");
            dialogid.find("#wrong-data").hide();

            // Request list of advisors.
            $.ajax({
               method: "POST",
               url: "controller.php",
               data: {cmd: "user/SelectAdvisors"},
               dataType: "json",
               success: function (data) {
                  advisorNameSelectmenu.selectmenu("destroy");
                  advisorNameSelectmenu.empty();
                  for (var key in data) {
                     advisorNameSelectmenu.append($("<option>").attr("value", key).text(data[key]));
                  }
                  selectedAdvisorName = advisorNameSelectmenu.val();
                  app.createComboBox(dialogid.find("#select-advisor-name"), 180, 100, function (event, ui) {
                     selectedAdvisorName = ui.item.value;
                  });
                  advisorNameSelectmenu.val(selectedAdvisorName);
                  advisorNameSelectmenu.selectmenu("refresh");
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });

      app.createComboBox(dialogid.find("#select-user-type"), 180, 0, function (event, ui) {
         selectedUserType = ui.item.value;

         var advisorRow = dialogid.find("#advisor-row");
         if (selectedUserType === "TRANSLATOR") {
            advisorRow.show();
         } else {
            advisorRow.hide();
         }
      });

      app.createComboBox(dialogid.find("#select-user-gender"), 180, 0, function (event, ui) {
         selectedUserGender = ui.item.value;
      });

      app.createComboBox(dialogid.find("#select-advisor-name"), 180, 100, function (event, ui) {
         selectedAdvisorName = ui.item.value;
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property modifyUserDialog
       * @type {Object}
       */
      app.newUserDialog = {
         /**
          * Returns selected user type.
          * @method getSelectedType
          * @return {String} TRANSLATOR, ADVISOR, or ADMIN.
          */
         getSelectedType: function () {
            return selectedUserType;
         },
         /**
          * Returns selected user gender.
          * @method getSelectedGender
          * @return {String} MALE, or FEMALE.
          */
         getSelectedGender: function () {
            return selectedUserGender;
         },
         /**
          * Returns selected advisor ID.
          * @method getSelectedGender
          * @return {Integer} A user ID.
          */
         getSelectedAdvisor: function () {
            return selectedAdvisorName;
         }
      };
   };

   /**
    * Creates a dialog to modify a user account.
    *
    * @method createUpdateUserDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createUpdateUserDialog = function (dialogid, width) {
      var selectedUserType = "TRANSLATOR";
      var selectedUserGender = "MALE";
      var selectedAdvisorName = 0;
      var numberOfAdvisors = 0;
      
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getOkCaption(),
               click: function () {
                  app.updateUser($(this));
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "user/SelectUser",
                      username: {field: "username",
                                 value: app.userTable.getSelectedUsername()}
               },
               success: function (data) {
                  var userTypeSelectmenu = dialogid.find("#select-user-type");
                  var userGenderSelectmenu = dialogid.find("#select-user-gender");
                  var advisorNameSelectmenu = dialogid.find("#select-advisor-name");
                  numberOfAdvisors = Object.keys(data.advisors).length;
                  
                  // Set user data.
                  dialogid.find("input[name=username]").val(data.user.username);
                  dialogid.find("input[name=password]").val("");
                  dialogid.find("input[name=passwordv]").val("");
                  selectedUserType = data.user.type;
                  userTypeSelectmenu.val(selectedUserType);
                  userTypeSelectmenu.selectmenu("refresh");
                  dialogid.find("input[name=name]").val(data.user.firstName);
                  selectedUserGender = data.user.gender;
                  userGenderSelectmenu.val(selectedUserGender);
                  userGenderSelectmenu.selectmenu("refresh");
                  dialogid.find("input[name=email]").val(data.user.email);

                  // Set advisor users.
                  if (numberOfAdvisors > 0) {
                     advisorNameSelectmenu.selectmenu("destroy");
                     advisorNameSelectmenu.empty();
                     for (var key in data.advisors) {
                        advisorNameSelectmenu.append($("<option>").attr("value", key).text(data.advisors[key]));
                     }
                     if (data.user.advisor === null) {
                        selectedAdvisorName = advisorNameSelectmenu.val();
                     } else {
                        selectedAdvisorName = data.user.advisor;
                     }
                     app.createComboBox(dialogid.find("#select-advisor-name"), 180, 100, function (event, ui) {
                        selectedAdvisorName = ui.item.value;
                     });
                     advisorNameSelectmenu.val(selectedAdvisorName);
                     advisorNameSelectmenu.selectmenu("refresh");
                  }
                  
                  var advisorRow = dialogid.find("#advisor-row");
                  if (selectedUserType === "TRANSLATOR") {
                     advisorRow.show();
                  } else {
                     advisorRow.hide();
                  }
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });

      app.createComboBox(dialogid.find("#select-user-type"), 180, 0, function (event, ui) {
         selectedUserType = ui.item.value;

         var advisorRow = dialogid.find("#advisor-row");
         if (selectedUserType === "TRANSLATOR" && numberOfAdvisors > 0) {
            advisorRow.show();
         } else {
            advisorRow.hide();
         }
      });

      app.createComboBox(dialogid.find("#select-user-gender"), 180, 0, function (event, ui) {
         selectedUserGender = ui.item.value;
      });

      app.createComboBox(dialogid.find("#select-advisor-name"), 180, 100, function (event, ui) {
         selectedAdvisorName = ui.item.value;
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property modifyUserDialog
       * @type {Object}
       */
      app.modifyUserDialog = {
         /**
          * Returns selected user type.
          * @method getSelectedType
          * @return {String} TRANSLATOR, ADVISOR, or ADMIN.
          */
         getSelectedType: function () {
            return selectedUserType;
         },
         /**
          * Returns selected user gender.
          * @method getSelectedGender
          * @return {String} MALE, or FEMALE.
          */
         getSelectedGender: function () {
            return selectedUserGender;
         },
         /**
          * Returns selected advisor ID.
          * @method getSelectedGender
          * @return {Integer} A user ID.
          */
         getSelectedAdvisor: function () {
            return selectedAdvisorName;
         }
      };
   };

   /**
    * Creates a dialog to delete a user account.
    *
    * @method createDeleteUserDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createDeleteUserDialog = function (dialogid, width) {
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getOkCaption(),
               click: function () {
                  app.deleteUser($(this));
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            var text = dialogid.find("p").html();
            text = text.replace(/«\S+»/, "«" + app.userTable.getSelectedUsername() + "»");
            dialogid.find("p").html(text);
            dialogid.find("#wrong-data").hide();
         }
      });
   };

   /**
    * Creates a dialog that allows the user to modify his account.
    *
    * @method createUpdateCurrentUserDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createUpdateCurrentUserDialog = function (dialogid, width) {
      var selectedUserGender = "MALE";
      var selectedTheme = "hot-sneaks";
      
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getOkCaption(),
               click: function () {
                  app.updateCurrentUser($(this));
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "user/SelectUser",
                      username: {field: "username",
                                 value: $("#user-button").children().last().html()}
               },
               success: function (data) {
                  var userGenderSelectmenu = dialogid.find("#select-user-gender");
                  var themeSelectmenu = dialogid.find("#select-ui-theme");
                  
                  // Set user data.
                  dialogid.find("input[name=username]").val(data.user.username);
                  dialogid.find("input[name=password]").val("");
                  dialogid.find("input[name=passwordv]").val("");
                  dialogid.find("input[name=name]").val(data.user.firstName);
                  selectedUserGender = data.user.gender;
                  userGenderSelectmenu.val(selectedUserGender);
                  userGenderSelectmenu.selectmenu("refresh");
                  dialogid.find("input[name=email]").val(data.user.email);
                  
                  themeSelectmenu.selectmenu("destroy");
                  themeSelectmenu.empty();
                  for (var key in data.themes) {
                     themeSelectmenu.append($("<option>").attr("value", key).text(data.themes[key]));
                  }
                  selectedTheme = app.getLocalization().getTheme();
 
                  app.createComboBox(dialogid.find("#select-ui-theme"), 180, 100, function (event, ui) {
                     selectedTheme = ui.item.value;
                  });
                  themeSelectmenu.val(selectedTheme);
                  themeSelectmenu.selectmenu("refresh");
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });
                  
      app.createComboBox(dialogid.find("#select-user-gender"), 180, 0, function (event, ui) {
         selectedUserGender = ui.item.value;
      });
      
      app.createComboBox(dialogid.find("#select-ui-theme"), 180, 0, function (event, ui) {
         selectedTheme = ui.item.value;
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property modifyCurrentUserDialog
       * @type {Object}
       */
      app.modifyCurrentUserDialog = {
         /**
          * Returns selected user gender.
          * @method getSelectedGender
          * @return {String} MALE, or FEMALE.
          */
         getSelectedGender: function () {
            return selectedUserGender;
         },
         /**
          * Returns selected UI theme.
          * @method getSelectedGender
          * @return {String} A jQuery UI theme name.
          */
         getSelectedTheme: function () {
            return selectedTheme;
         }
      };
   };
   
   /**
    * Creates a dialog to show help about an ESO code.
    *
    * @method createEsoCodeDialog
    * @param {jQuery} dialogid A jQuery selector.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createEsoCodeDialog = function (dialogid, width) {
      var esocode = "";

      dialogid.dialog({
         autoOpen: false,
         width: width,
         open: function () {
            $(this).dialog("option", "title", app.getLocalization().getCodeCaption() + ' ' + esocode.replace(/&lt;/g, '<').replace(/&gt;/g, '>'));
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "html",
               data: {cmd: "translation/GetCodeExplanation",
                      code: {field: "code", value: esocode}
               },
               success: function (data) {
                  dialogid.children().html("<p>" + data + "</p>");
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property esoCodeDialog
       * @type {Object}
       */
      app.esoCodeDialog = {
         /**
          * Sets the ESO code that will be explained in the dialog.
          * @method setCode
          * @param {String} code A string like <<1>>, <<C:1>>, <<npc{.../...}>>, and so on.
          */
         setCode: function (code) {
            esocode = code;
         }
      };
   };

   /**
    * Creates a dialog to add a new term to the glossary.
    *
    * @method createNewTermDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createNewTermDialog = function (dialogid, width) {
      var selectedTermType = 1;
      var translationData = {};

      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {id: "button-ok",
             text: app.getLocalization().getOkCaption(),
               click: function () {
                  $(this).parent().find("#button-ok").attr("disabled", true);
                  $(this).find("#dialog-ajax-loader").show();
                  app.insertTerm($(this), app.newTermDialog.getTranslation());
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            dialogid.find("#dialog-ajax-loader").hide();
            dialogid.find("#counter-info").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "translation/SelectTermTypes"},
               success: function (data) {
                  var termTypeSelectmenu = dialogid.find("#select-term-type");
                  termTypeSelectmenu.selectmenu("destroy");
                  termTypeSelectmenu.empty();
                  for (var key in data) {
                     termTypeSelectmenu.append($("<option>").attr("value", key).text(data[key]));
                  }
                  app.createComboBox(dialogid.find("#select-term-type"), 350, 100, function (event, ui) {
                     selectedTermType = ui.item.value;
                  });
                  
                  dialogid.find("input[name=term]").val("");
                  dialogid.find("input[name=plural]").val("");
                  dialogid.find("input[name=translation]").val("");
                  dialogid.find("textarea[name=note]").val("");
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });
                    
      app.createComboBox(dialogid.find("#select-term-type"), 350, 100, function (event, ui) {
         selectedTermType = ui.item.value;
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property newTermDialog
       * @type {Object}
       */
      app.newTermDialog = {
         /**
          * Returns the type of a selected term.
          * @method getSelectedTermType
          * @return {String} A localized string.
          */
         getSelectedTermType: function () {
            return selectedTermType;
         },
         /**
          * Sets the jQuery object that selects glossary row, and string identifiers.
          * @param {Object} data A translation object.
          */
         setTranslation: function (data) {
            translationData = data;
         },
         /**
          * Gets the jQuery object that selects glossary row, and string identifiers.
          * @return {Object} A translation object.
          */
         getTranslation: function () {
            return translationData;
         }
      };
   };

   /**
    * Creates a dialog to add a new term to the glossary.
    *
    * @method createUpdateTermDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createUpdateTermDialog = function (dialogid, width) {
      var selectedTermType = 1;
      var selectedLockedTerm = 0;
      var termid = null;
      var term = null;
      var translationData = {};

      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {id: "button-ok",
             text: app.getLocalization().getOkCaption(),
               click: function () {
                  $(this).parent().find("#button-ok").attr("disabled", true);
                  $(this).find("#dialog-ajax-loader").show();
                  $(this).find("#delete-term").attr("disabled", true);
                  app.updateTerm($(this), app.updateTermDialog.getTranslation());
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            dialogid.find("#dialog-ajax-loader").hide();
            dialogid.find("#counter-info").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "translation/SelectTermTypes"},
               success: function (data) {
                  var termTypeSelectmenu = dialogid.find("#select-term-type");
                  termTypeSelectmenu.selectmenu("destroy");
                  termTypeSelectmenu.empty();
                  for (var key in data) {
                     termTypeSelectmenu.append($("<option>").attr("value", key).text(data[key]));
                  }
                  app.createComboBox(dialogid.find("#select-term-type"), 350, 100, function (event, ui) {
                     selectedTermType = ui.item.value;
                  });

                  $.ajax({
                     method: "POST",
                     url: "controller.php",
                     dataType: "json",
                     data: {cmd: "translation/SelectTerm",
                            termid: {field: "termid",
                                     value: app.updateTermDialog.getTermId()}
                     },
                     success: function (data) {
                        var termTypeSelectmenu = dialogid.find("#select-term-type");
                        var lockedTermSelectmenu = dialogid.find("#select-locked-term");
                        
                        dialogid.find("input[name=termid]").val(data.termid);
                        dialogid.find("input[name=term]").val(data.term);
                        var inputPlural = dialogid.find("input[name=plural]");
                        // Disabled plural when it is defined.
                        inputPlural.val(data.plural);
                        if (data.plural !== undefined) {
                           inputPlural.attr("disabled", "disabled");
                        } else if (inputPlural.attr("disabled") !== undefined) {
                           inputPlural.removeAttr("disabled");
                        }
                        dialogid.find("input[name=translation]").val(data.translation);
                        dialogid.find("textarea[name=note]").val(data.note);
                        selectedTermType = data.typeid;
                        termTypeSelectmenu.val(selectedTermType);
                        termTypeSelectmenu.selectmenu("refresh");
                        selectedLockedTerm = data.locked;
                        lockedTermSelectmenu.val(selectedLockedTerm);
                        lockedTermSelectmenu.selectmenu("refresh");
                     },
                     error: function (e, xhr) {
                        console.log(xhr);
                     }
                  });
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });

      app.createComboBox(dialogid.find("#select-term-type"), 350, 100, function (event, ui) {
         selectedTermType = ui.item.value;
      });
      app.createComboBox(dialogid.find("#select-locked-term"), 350, 0, function (event, ui) {
         selectedLockedTerm = ui.item.value;
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property updateTermDialog
       * @type {Object}
       */
      app.updateTermDialog = {
         /**
          * Sets a term ID to update.
          * @method setTermId
          * @param {Integer} data A term ID.
          */
         setTermId: function (data) {
            termid = data;
         },
         /**
          * Returns a term ID to update.
          * @method getTermId
          * @return {Integer} A term ID.
          */
         getTermId: function () {
            return termid;
         },
         /**
          * Sets a term to update.
          * @method setTerm
          * @param {String} data A localized string.
          */
         setTerm: function (data) {
            term = data;
         },
         /**
          * Returns a term to update.
          * @method getTerm
          * @return {String} A localized string.
          */
         getTerm: function () {
            return term;
         },
         /**
          * Returns the type of a selected term.
          * @method getSelectedTermType
          * @return {String} A localized string.
          */
         getSelectedTermType: function () {
            return selectedTermType;
         },
         /**
          * Returns whether a selected term is locked.
          * @method getSelectedTermType
          * @return {String} A localized string.
          */
         getSelectedLockedTerm: function () {
            return selectedLockedTerm;
         },
         /**
          * Sets the jQuery object that selects glossary row, and string identifiers.
          * @param {Object} data A translation object.
          */
         setTranslation: function (data) {
            translationData = data;
         },
         /**
          * Gets the jQuery object that selects glossary row, and string identifiers.
          * @return {Object} A translation object.
          */
         getTranslation: function () {
            return translationData;
         }
      };
   };
   
   /**
    * Creates a dialog to create a new task.
    *
    * @method createNewTaskDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createNewTaskDialog = function (dialogid, width) {
      var selectedTaskType = "TRANSLATION";
      var selectedTaskMode = 1; // Selection mode by string status
      var selectedTaskUser = -1;
      
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {id: "button-ok",
             text: app.getLocalization().getOkCaption(),
               click: function () {
                  $(this).parent().find("#button-ok").attr("disabled", true);
                  $(this).find("#dialog-ajax-loader").show();
                  app.insertTask($(this));
               }},
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            dialogid.find("#dialog-ajax-loader").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "task/SelectPupils"},
               success: function (data) {
                  if (data.result === 'SESSION_IS_NOT_SET') {
                     location.reload();
                  } else {
                     var taskModeSelectmenu = dialogid.find("#select-task-mode");
                     var taskUserSelectmenu = dialogid.find("#select-task-user");

                     // If updating taks can't be created, remove that task type option.
                     if (!app.areUpdatingTasksAvailable) {
                        var taskTypeSelectmenu = dialogid.find("#select-task-type");
                        taskTypeSelectmenu.children().last().remove();
                        taskTypeSelectmenu.selectmenu("refresh");
                     }
                     
                     // Sets the dialog to its initial state.
                     selectedTaskMode = "1"; // Selection mode by string status
                     taskModeSelectmenu.val(selectedTaskMode);
                     taskModeSelectmenu.selectmenu("refresh");

                     selectedTaskUser = -1; // Current user
                     taskUserSelectmenu.selectmenu("destroy");
                     taskUserSelectmenu.empty();
                     for (var key in data) {
                        taskUserSelectmenu.append($("<option>").attr("value", data[key]).text(key));
                     }
                     app.createComboBox(dialogid.find("#select-task-user"), 190, 100, function (event, ui) {
                        selectedTaskUser = ui.item.value;
                     });

                     dialogid.find("input[name=tableid]").val("");
                     dialogid.find("input[name=tablenumber]").val("");
                     dialogid.find("input[name=count]").val("");
                  }
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });

      app.createComboBox(dialogid.find("#select-task-type"), 190, 0, function (event, ui) {
         selectedTaskType = ui.item.value;
         
         var tableRow = dialogid.find("#table-row");
         var buildingModeRow = dialogid.find("#building-mode-row");
         
         // Updating task only need a user, and a number of strings. There's no selection mode 
         // because it's always "by string status".
         if (selectedTaskType === "UPDATING") {
            dialogid.find("input[name=tableid]").val("0");
            tableRow.hide();
            buildingModeRow.hide();
         }
         else {
            tableRow.show();
            buildingModeRow.show();
            
            var taskModeSelectmenu = dialogid.find("#select-task-mode");
            taskModeSelectmenu.val(selectedTaskMode);
            taskModeSelectmenu.selectmenu("refresh");
         }
      });
      
      app.createComboBox(dialogid.find("#select-task-mode"), 190, 0, function (event, ui) {
         selectedTaskMode = ui.item.value;
      });
      
      app.createComboBox(dialogid.find("#select-task-user"), 190, 0, function (event, ui) {
         selectedTaskUser = ui.item.value;
      });

      /**
       * Allows to handle (set/get) values of the dialog.
       * @property newTaskDialog
       * @type {Object}
       */
      app.newTaskDialog = {
         /**
          * Returns the type of task.
          * @method getSelectedTermType
          * @return {String} A localized string.
          */
         getSelectedTaskType: function () {
            return selectedTaskType;
         },
         /**
          * Returns the selection mode of strings.
          * @method getSelectedTaskMode
          * @return {Integer} 1 (status), 2 (offset-inc), or 3 (offset-exc).
          */
         getSelectedTaskMode: function () {
            return selectedTaskMode;
         },
         /**
          * Returns the user who the task is assigned to.
          * @method getSelectedTaskUser
          * @return {Integer} A user ID.
          */
         getSelectedTaskUser: function () {
            return selectedTaskUser;
         }
      };
   };
   
   /**
    * Creates a dialog to select an ESO table for a task.
    *
    * @method createSelectEsoTableDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createSelectEsoTableDialog = function (dialogid, width) {     
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {id: "button-ok",
             text: app.getLocalization().getOkCaption(),
               click: function () {
                  $("#new-task-dialog").find("input[name=tableid]").val(app.briefEsoIndexTable.getSelectedTableId());
                  $("#new-task-dialog").find("input[name=tablenumber]").val(app.briefEsoIndexTable.getSelectedTableNumber());
                  $(this).dialog("close");
               }
            },
            {id: "button-cancel",
             text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }
            }
         ]
      });
   };
   
   /**
    * UpdateTaskDialog is a simple dialog to change a task.
    *
    * @class UpdateTaskDialog
    * @constructor
    *
    * @param {Rocinante} app A Rocinante object.
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    * @param {Table} table A task table.
    * @param {String} cmd The name of the request that will be used to update the task.
    * @param {Function} onOkClick What to do when OK button is clicked.
    */
   function UpdateTaskDialog(app, dialogid, width, table, cmd, onOkClick) {
      var selectedTaskUser = -1;
      
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {id: "button-ok",
             text: app.getLocalization().getOkCaption(),
               click: onOkClick
               },
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }}
         ],
         open: function () {
            var taskid = table.getSelectedTaskId();
            dialogid.find("#wrong-data").hide();
            dialogid.find("#dialog-ajax-loader").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: cmd, 
                      taskid: {field: "taskid", value: taskid},
                      type: {field: "type", value: "TRANSLATION"}
               },
               success: function (data) {
                  if (data.result === 'SESSION_IS_NOT_SET') {
                     location.reload();
                  } else {
                     selectedTaskUser = data.userid;

                     dialogid.find("input[name=taskid]").val(taskid);

                     var taskUserSelectmenu = dialogid.find("#select-task-user");
                     taskUserSelectmenu.selectmenu("destroy");
                     taskUserSelectmenu.empty();
                     for (var key in data.pupils) {
                        taskUserSelectmenu.append($("<option>").attr("value", data.pupils[key]).text(key));
                     }
                     app.createComboBox(taskUserSelectmenu, 190, 100, function (event, ui) {
                        selectedTaskUser = ui.item.value;
                     });
                     taskUserSelectmenu.val(selectedTaskUser);
                     taskUserSelectmenu.selectmenu("refresh");
                  }
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });
      
      app.createComboBox(dialogid.find("#select-task-user"), 190, 0, function (event, ui) {
         selectedTaskUser = ui.item.value;
      });

      /**
       * Returns the user who the task is assigned to.
       * @method getSelectedTaskUser
       * @return {Integer} A user ID.
       */
      this.getSelectedTaskUser = function () {
         return selectedTaskUser;
      };
   }
   
   /**
    * Creates a dialog to update a task.
    *
    * @method createModifyTaskDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    * @param {Table} table A task table.
    * @param {String} cmd The name of the request that will be used to update the task.
    * @param {Function} onOkClick What to do when OK button is clicked.
    */
   app.createModifyTaskDialog = function(dialogid, width, table, cmd, onOkClick) {
      return new UpdateTaskDialog(app, dialogid, width, table, cmd, onOkClick);
   };
   
   /**
    * DeleteTaskDialog is a simple dialog to delete a task.
    *
    * @class DeleteTaskDialog
    * @constructor
    *
    * @param {Rocinante} app A Rocinante object.
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    * @param {Function} getTaskId A function that returns the selected task.
    * @param {Function} onOkClick What to do when OK button is clicked.
    */
   function DeleteTaskDialog(app, dialogid, width, getTaskId, onOkClick) {
      var taskid = -1;
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {id: "button-ok",
             text: app.getLocalization().getOkCaption(),
             click: onOkClick
             },
            {text: app.getLocalization().getCancelCaption(),
             click: function () {
               $(this).dialog("close");
             }}
         ],
         open: function () {
            taskid = getTaskId();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "task/SelectTaskStatus", 
                      taskid: {field: "taskid",
                               value: taskid}},
               success: function (data) {
                  dialogid.find("p").html(data.message);
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });
      
      /**
       * Returns the user who the task is assigned to.
       * @method getTaskId
       * @return {Integer} A user ID.
       */
      this.getTaskId = function () {
         return taskid;
      };
   }
   
   /**
    * Creates a dialog to delete a task.
    *
    * @method createDeleteTaskDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    * @param {Function} getTaskId A function that returns the selected task.
    * @param {Function} onOkClick What to do when OK button is clicked.
    */
   app.createDeleteTaskDialog = function(dialogid, width, getTaskId, onOkClick) {
      return new DeleteTaskDialog(app, dialogid, width, getTaskId, onOkClick);
   };
   
   /**
    * Creates a dialog to write a new message.
    *
    * @method createNewMessageDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createNewMessageDialog = function (dialogid, width) {
      dialogid.find("#wrong-data").hide();
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getSendCaption(),
               click: function () {
                  app.insertMessage($(this));
               }
            },
            {text: app.getLocalization().getSaveCaption(),
               click: function () {
                  app.insertDraft($(this));
               }
            },
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }
            }
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            dialogid.find("input[name=addressees]").val("").focus();
            dialogid.find("input[name=subject]").val("");
            dialogid.find("input[name=chatid]").val(0);
            dialogid.find("input[name=isdraft]").val(0);
            dialogid.find("textarea[name=body]").val("");
         }
      });
   };
   
   /**
    * Creates a dialog to write a reply to a message.
    *
    * @method createNewReplyDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createNewReplyDialog = function (dialogid, width) {
      var replyMode = 0; // 0 - reply; 1 - reply all
      dialogid.find("#wrong-data").hide();
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getSendCaption(),
               click: function () {
                  app.insertMessage($(this));
               }
            },
            {text: app.getLocalization().getSaveCaption(),
               click: function () {
                  app.insertDraft($(this));
               }
            },
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }
            }
         ],
         open: function () {
            var addressees = app.inboxAccordion.getSelection().object.addressees;
            if (replyMode === 0) {
               addressees = addressees.split(/[ ,]+/,1);
            }
            dialogid.find("input[name=addressees]").val(addressees);
            dialogid.find("input[name=subject]").val(app.inboxAccordion.getSelection().object.subject);
            dialogid.find("input[name=chatid]").val(app.inboxAccordion.getSelection().object.chatid);
            dialogid.find("input[name=isdraft]").val(0);
            dialogid.find("textarea[name=body]").focus();
         }
      });
      
      /**
       * Allows to handle (set/get) values of the panel.
       * @property newReplyDialog
       * @type {Object}
       */
      app.newReplyDialog = {
         /**
          * Sets reply mode.
          * @method getReplyMode
          * @return {Integer} 0, simple reply; 1, reply all.
          */
         getReplyMode: function () {
            return replyMode;
         },
         /**
          * Returns reply mode.
          * @method setReplyMode
          * @param {Integer} mode 0, simple reply; 1, reply all.
          */
         setReplyMode: function (mode) {
            replyMode = mode;
         }
      };
   };
        
   /**
    * Creates a dialog to write a messag from a saved draft.
    *
    * @method createNewMessageFromDraftDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createNewMessageFromDraftDialog = function (dialogid, width) {
      dialogid.find("#wrong-data").hide();
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getSendCaption(),
               click: function () {
                  app.insertMessage($(this));
               }
            },
            {text: app.getLocalization().getSaveCaption(),
               click: function () {
                  app.insertDraft($(this));
               }
            },
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }
            }
         ],
         open: function () {
            dialogid.find("input[name=addressees]").val(app.draftsAccordion.getSelection().object.addressees);
            dialogid.find("input[name=subject]").val(app.draftsAccordion.getSelection().object.subject);
            dialogid.find("input[name=chatid]").val(app.draftsAccordion.getSelection().object.chatid);
            dialogid.find("input[name=isdraft]").val(app.draftsAccordion.getSelection().object.mailid);
            dialogid.find("textarea[name=body]").val(app.draftsAccordion.getSelection().object.body).focus();
         }
      });
   };
   
   /**
    * CreateSelectUserDialog is a simple dialog to select an user.
    *
    * @class CreateSelectUserDialog
    * @constructor
    *
    * @param {Rocinante} app A Rocinante object.
    * @param {jQuery} dialogid A jQuery object that selects a <div> element with a dialog that lists
    * the users.
    * @param {Number} width The dialog width given in pixels.
    * @param {jQuery} messageDialog A jQuery object that selects a <div> element with a dialog that
    * allows the user to write a message.
    * @param {Table} table A table that stores the users.
    */
   function CreateSelectUserDialog(app, dialogid, width, messageDialog, table) {
      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {id: "button-ok",
             text: app.getLocalization().getOkCaption(),
               click: function () {
                  var list = messageDialog.find("input[name=addressees]").val();
                  if (list.length === 0 || list.endsWith(', ') || list.endsWith(',')) {
                     list += table.getSelectedUsername();
                  } else {
                     list += ', ' + table.getSelectedUsername();
                  }
                  
                  messageDialog.find("input[name=addressees]").val(list);
                  $(this).dialog("close");
               }
            },
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }
            }
         ]
      });
   };
   
   /**
    * Creates a dialog to select an addreessee of a message.
    *
    * @method createSelectUserDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element with a dialog that lists
    * the users.
    * @param {Number} width The dialog width given in pixels.
    * @param {jQuery} messageDialog A jQuery object that selects a <div> element with a dialog that
    * allows the user to write a message.
    * @param {Table} table A table that stores the users.
    */
   app.createSelectUserDialog = function(dialogid, width, messageDialog, table) {
      return new CreateSelectUserDialog(app, dialogid, width, messageDialog, table);
   };
   
   /**
    * Creates a panel to generate the add-on for the game.
    * 
    * @method createGenerateAddonPanel
    */
   app.createGenerateAddonPanel = function () {
      var selectedExtraFilesMode = "NO_EXTRAFILES";
      var selectedExtraFilesArchive = {};
      var selectedClientHeaderFile = {};
      var selectedPregameHeaderFile = {};

      $("#extrafiles-row").hide();

      $("#browse-extra-files").on("change", function (event) {
         selectedExtraFilesArchive = event.target.files; // JS FileList
         $("#extrafiles").val(selectedExtraFilesArchive[0].name); // file[0] is a JS File
      });

      $("#select-extra-files").click(function (event) {
         event.preventDefault();
         $("#browse-extra-files").trigger('click');
      });
      
      $("#browse-clientheader-file").on("change", function (event) {
         selectedClientHeaderFile = event.target.files;
         $("#clientheader").val(selectedClientHeaderFile[0].name);
      });

      $("#select-clientheader-file").click(function (event) {
         event.preventDefault();
         $("#browse-clientheader-file").trigger('click');
      });
      
      $("#browse-pregameheader-file").on("change", function (event) {
         selectedPregameHeaderFile = event.target.files;
         $("#pregameheader").val(selectedPregameHeaderFile[0].name);
      });

      $("#select-pregameheader-file").click(function (event) {
         event.preventDefault();
         $("#browse-pregameheader-file").trigger('click');
      });

      $("#generate-addon-button").click(function (event) {
         app.generateAddon(selectedExtraFilesMode, selectedExtraFilesArchive, selectedClientHeaderFile, selectedPregameHeaderFile, $("#version").val());
         event.stopPropagation();
         event.preventDefault();
      });
   
      app.createComboBox($("#select-extrafiles-mode"), 180, 0, function (event, ui) {
         selectedExtraFilesMode = ui.item.value;
         if (selectedExtraFilesMode === "NO_EXTRAFILES") {
            $("#extrafiles-row").hide();
         } else {
            $("#extrafiles-row").show();
         }
      });
      
      /**
       * Allows to handle (set/get) values of the panel.
       * @property GenerateAddonPanel
       * @type {Object}
       */
      app.GenerateAddonPanel = {
         /**
          * Returns the extra file mode.
          * @method getSelectedExtraFilesMode
          * @return {String} NO_EXTRAFILES, ADD_EXTRAFILES, or DELETE_EXTRAFILES.
          */
         getSelectedExtraFilesMode: function () {
            return selectedExtraFilesMode;
         },
         /**
          * Returns the name of archive that contains the extra files.
          * @method getSelectedFile
          * @return {FileList} An object that contains the selected files.
          */
         getSelectedFile: function () {
            return selectedExtraFilesArchive;
         }
      };
   };
   
   /**
    * Creates a dialog to handle maintenance mode.
    *
    * @method createMaintenanceModeDialog
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Number} width The dialog width given in pixels.
    */
   app.createMaintenanceModeDialog = function (dialogid, width) {
      var selectedMaintenanceMode = "OFF";

      dialogid.dialog({
         autoOpen: false,
         width: width,
         buttons: [
            {text: app.getLocalization().getOkCaption(),
               click: function () {
                  app.updateMaintenanceMode($(this));
                  $(this).dialog("close");
               }
            },
            {text: app.getLocalization().getCancelCaption(),
               click: function () {
                  $(this).dialog("close");
               }
            }
         ],
         open: function () {
            dialogid.find("#wrong-data").hide();
            $.ajax({
               method: "POST",
               url: "controller.php",
               dataType: "json",
               data: {cmd: "SelectMaintenanceMode"},
               success: function (data) {
                  var maintenanceModeSelectmenu = dialogid.find("#select-maintenance-mode");
                  selectedMaintenanceMode = data.status;
                  maintenanceModeSelectmenu.val(selectedMaintenanceMode);
                  maintenanceModeSelectmenu.selectmenu("refresh");
                  dialogid.find("textarea[name=message]").val(data.message);
               },
               error: function (e, xhr) {
                  console.log(xhr);
               }
            });
         }
      });
      
      app.createComboBox(dialogid.find("#select-maintenance-mode"), 210, 0, function (event, ui) {
         selectedMaintenanceMode = ui.item.value;
      });
      
      /**
       * Allows to handle (set/get) values of the dialog.
       * @property maintenanceModeDialog
       * @type {Object}
       */
      app.maintenanceModeDialog = {
         /**
          * Returns maintenance mode status.
          * @method getSelectedTaskMode
          * @return {String} ACTIVATE, or DEACTIVATE.
          */
         getSelectedMode: function () {
            return selectedMaintenanceMode;
         }
      };
   };
   
   /**
    * Creates a panel to update the database.
    * 
    * @method createUpdateDatabasePanel
    */
   app.createUpdateDatabasePanel = function () {
      var selectedEnClientFile = {};
      var selectedFrClientFile = {};
      var selectedEnPregameFile = {};
      var selectedFrPregameFile = {};
      var selectedEnLangFile = {};
      var selectedFrLangFile = {};
      
      $("#browse-update-files-row-1").hide();
      $("#browse-update-files-row-2").hide();
      $("#browse-update-files-row-3").hide();
      
      $("#browse-en-client").on("change", function (event) {
         selectedEnClientFile = event.target.files;
         $("#clienten").val(selectedEnClientFile[0].name);
      });

      $("#select-en-client").click(function (event) {
         event.preventDefault();
         $("#browse-en-client").trigger('click');
      });

      $("#browse-fr-client").on("change", function (event) {
         selectedFrClientFile = event.target.files;
         $("#clientfr").val(selectedFrClientFile[0].name);
      });

      $("#select-fr-client").click(function (event) {
         event.preventDefault();
         $("#browse-fr-client").trigger('click');
      });
      
      $("#browse-en-pregame").on("change", function (event) {
         selectedEnPregameFile = event.target.files;
         $("#pregameen").val(selectedEnPregameFile[0].name);
      });

      $("#select-en-pregame").click(function (event) {
         event.preventDefault();
         $("#browse-en-pregame").trigger('click');
      });
      
      $("#browse-fr-pregame").on("change", function (event) {
         selectedFrPregameFile = event.target.files;
         $("#pregamefr").val(selectedFrPregameFile[0].name);
      });

      $("#select-fr-pregame").click(function (event) {
         event.preventDefault();
         $("#browse-fr-pregame").trigger('click');
      });
      
      $("#browse-en-lang").on("change", function (event) {
         selectedEnLangFile = event.target.files;
         $("#langen").val(selectedEnLangFile[0].name);
      });

      $("#select-en-lang").click(function (event) {
         event.preventDefault();
         $("#browse-en-lang").trigger('click');
      });
      
      $("#browse-fr-lang").on("change", function (event) {
         selectedFrLangFile = event.target.files;
         $("#langfr").val(selectedFrLangFile[0].name);
      });

      $("#select-fr-lang").click(function (event) {
         event.preventDefault();
         $("#browse-fr-lang").trigger('click');
      });
      
      $("#update-database-button").click(function (event) {
         app.startDatabaseUpdate();
         event.stopPropagation();
         event.preventDefault();
      });
      
      /**
       * Allows to handle (set/get) values of the panel.
       * @property UpdateDatabasePanel
       * @type {Object}
       */
      app.UpdateDatabasePanel = {
         /**
          * Returns en_client.lua
          * @method getSelectedEnClientFile
          * @return {FileList} An object that contains the selected files.
          */
         getSelectedEnClientFile: function () {
            return selectedEnClientFile[0];
         },
         /**
          * Returns fr_client.lua
          * @method getSelectedFrClientFile
          * @return {FileList} An object that contains the selected files.
          */
         getSelectedFrClientFile: function () {
            return selectedFrClientFile[0];
         },
         /**
          * Returns en_pregame.lua
          * @method getSelectedEnPregameFile
          * @return {FileList} An object that contains the selected files.
          */
         getSelectedEnPregameFile: function () {
            return selectedEnPregameFile[0];
         },
         /**
          * Returns fr_pregame.lua
          * @method getSelectedFrPregameFile
          * @return {FileList} An object that contains the selected files.
          */
         getSelectedFrPregameFile: function () {
            return selectedFrPregameFile[0];
         },
         /**
          * Returns en.lang
          * @method getSelectedEnLangFile
          * @return {FileList} An object that contains the selected files.
          */
         getSelectedEnLangFile: function () {
            return selectedEnLangFile[0];
         },
         /**
          * Returns fr.lang
          * @method getSelectedFrLangFile
          * @return {FileList} An object that contains the selected files.
          */
         getSelectedFrLangFile: function () {
            return selectedFrLangFile[0];
         }
      };
   };
};

/**
 * A module that manages every type of event.
 *
 * @param {Rocinante} app A Rocinante object.
 */
Rocinante.modules.events = function (app) {

   /**
    * Binds a link with a dialog. When the link is clicked, the dialog will be shown. Actually the
    * link is a button. jQuery makes buttons from links and decorates form buttons.
    *
    * @method bindDialog
    * @param {jQuery} buttonid A jQuery object that selects a <button> element.
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.bindDialog = function (buttonid, dialogid) {
      buttonid.click(function (event) {
         dialogid.dialog("open");
         event.preventDefault();
      });
   };
   
   /**
    * Binds a link with the dialog that is used to reply messages. When the link is clicked, the 
    * dialog will be shown. 
    *
    * @method bindReplyDialog
    * @param {jQuery} buttonid A jQuery object that selects a <button> element.
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Integer} mode The reply mode: 0 for single reply, 1 for reply all.
    */
   app.bindReplyDialog = function (buttonid, dialogid, mode) {
      buttonid.click(function (event) {
         app.newReplyDialog.setReplyMode(mode);
         dialogid.dialog("open");
         event.preventDefault();
      });
   };

   /**
    * Binds a ESO code link with a dialog that explains what it is and how to translate it.
    *
    * @method bindEsoCodeDialog
    * @param {jQuery} buttonid A jQuery object that selects a <button> element.
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {String} code An ESO code.
    */
   app.bindEsoCodeDialog = function (buttonid, dialogid, code) {
      buttonid.click(function (event) {
         app.esoCodeDialog.setCode(code);
         dialogid.dialog("open");
         event.preventDefault();
      });
   };
   
   /**
    * Shows contents of a selected task in a new tab.
    * @param {Event} event Event data.
    */
   $("#open-task-button").click(function (event) {      
      var taskid = app.userTaskTable.getSelectedTaskId();
      if (!taskid) {
         taskid = app.assignerTaskTable.getSelectedTaskId();
      }
      var tabid = app.addTab($("#tab-list"), taskid, app.getLocalization().getTaskCaption() + taskid, true);
      $("#tab-list").parent().tabs("option", "active", -1);
      app.createTaskTable(tabid, taskid);      
      event.preventDefault();
   });
   
   /**
    * Shows contents of a selected task by an admin in a new tab.
    * @param {Event} event Event data.
    */
   $("#open-task-admin-button").click(function (event) {      
      var taskid = app.adminTaskTable.getSelectedTaskId();
      var tabid = app.addTab($("#tab-list"), taskid, app.getLocalization().getTaskCaption() + taskid, true);
      $("#tab-list").parent().tabs("option", "active", -1);
      app.createTaskTable(tabid, taskid);      
      event.preventDefault();
   });
   
   /**
    * Requests deletion of the selected message/draft.
    * @param {Event} event Event data.
    */
   $("#delete-message-button").click(function (event) {
      var active = $("#mail-tabs").tabs("option", "active");
      var mailid = 0;
      
      switch (active) {
         case 0: // INBOX
            mailid = app.inboxAccordion.getSelection().object.mailid;
            app.deleteMessage(mailid);
            break;
         case 1: // OUTBOX
            mailid = app.outboxAccordion.getSelection().object.mailid;
            app.deleteMessage(mailid);
            break;
         case 2: // DRAFTS
            mailid = app.draftsAccordion.getSelection().object.mailid;
            app.deleteDraft(mailid);
            break;
      }

      event.preventDefault();      
   });
   
   /**
    * Requests deletion of a glossary term.
    * @param {Event} event Event data.
    */
   $("#delete-term").click(function (event) {
      app.deleteTerm($("#update-term-dialog"));
      event.preventDefault();  
   });

   /**
    * Collapses the last accordion that was activated after selecting a new mail tab.
    * @param {Event} event Event data.
    * @param {Object} ui ui.oldPanel stores the tab that was just deactivated.
    */
   $("#mail-tabs").on("tabsactivate", function (event, ui) {
      //if (ui.oldPanel.hasOwnProperty("accordion")) {
         ui.oldPanel.accordion('option', {active: false});
      //}
   });
   
   
   /**
    * Searchs for a given string when enter key is pressed.
    * @param {Event} event Event data.
    */
   $("input[name=search-text]").keypress(function (event) {
      var key = event.which;
      if (key === 13) { // the enter key code
         if ($("#search-lang-tab").length === 0) {
           var tabid = app.addTab($("#tab-list"), "search-lang-tab", app.getLocalization().getSearchLangCaption(), true, true);
            $("#tab-list").parent().tabs("option", "active", -1);
            $("#search-lang-tab").html("<div id='searching' style='text-align: center;'><img src='images/ajax-big-loader.gif' alt='searching'/></div>");
            app.createSearchLangTable(tabid);
         } else {
            $("#search-lang-tab").html("<div id='searching' style='text-align: center;'><img src='images/ajax-big-loader.gif' alt='searching'/></div>");
            app.createSearchLangTable("search-lang-tab");
         }
         
         if ($("#search-lua-tab").length === 0) {
           var tabid = app.addTab($("#tab-list"), "search-lua-tab", app.getLocalization().getSearchLuaCaption(), true, true);
            //$("#tab-list").parent().tabs("option", "active", -1);
            $("#search-lua-tab").html("<div id='searching' style='text-align: center;'><img src='images/ajax-big-loader.gif' alt='searching'/></div>");
            app.createSearchLuaTable(tabid);
         } else {
            $("#search-lua-tab").html("<div id='searching' style='text-align: center;'><img src='images/ajax-big-loader.gif' alt='searching'/></div>");
            app.createSearchLuaTable("search-lua-tab");
         }
         event.preventDefault();
      }
   }); 
   
   /**
    * Opens task tab.
    */
   $("#task-button").click(function () {
      $("#main-tabs").tabs("option", "active", 1);
   });
   
   /**
    * Opens mail tab and inbox tab.
    */
   $("#mail-button").click(function () {
      $("#main-tabs").tabs("option", "active", 2);
      $("#mail-tabs").tabs("option", "active", 0);
   });
   
   /**
    * Counts how many times a given term is in game text (new term dialog).
    * @param {Event} event Event data.
    */
   $("#count-new-singular-term").click(function (event) {
      app.countTerm($("#new-term-dialog"));
      event.preventDefault();
   });
   
   /**
    * Counts how many times a given term is in game text (new term dialog).
    * @param {Event} event Event data.
    */
   $("#count-new-plural-term").click(function (event) {
      app.countTerm($("#new-term-dialog"), true);
      event.preventDefault();
   });
   
   /**
    * Counts how many times a given term is in game text (modify term dialog).
    * @param {Event} event Event data.
    */
   $("#count-modify-singular-term").click(function (event) {
      app.countTerm($("#update-term-dialog"));
      event.preventDefault();
   });
   
   /**
    * Counts how many times a given term is in game text (modify term dialog).
    * @param {Event} event Event data.
    */
   $("#count-modify-plural-term").click(function (event) {
      app.countTerm($("#update-term-dialog"), true);
      event.preventDefault();
   });
   
   /**
    * Downloads the last version of the translation.
    * @param {Event} event Event data.
    */
   $("#download-button").click(function (event) {
      event.preventDefault();
      window.location.href = 'download.php';
   });
   
   /**
    * Closes the current session.
    * @param {Event} event Event data.
    */
   $("#logout-button").click(function (event) {
      app.logout();
   });
};

/**
 * A module that makes asynchronous requests to the server in order to read, write, and modify data.
 *
 * @param {Rocinante} app A Rocinante object.
 */
Rocinante.modules.ajax = function (app) {

   /**
    * Requests localization data to the server.
    *
    * @method requestLocalization
    */
   app.requestLocalization = function () {
      $.ajax({
         async: false,
         method: "POST",
         url: "controller.php",
         data: {cmd: "GetLocalization"},
         dataType: "json",
         success: function (data) {
            app.setLocalization(data);
            // Show a translation tip every 60 seconds.
            app.showTip();
            setInterval(app.showTip, 60000);
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests translation buttons to the server.
    *
    * @method requestLocalization
    */
   app.requestTranslationButtonSet = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "translation/GetButtonSet"},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               app.setTranslationButtonSet(data.html);
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests translation status string to the server.
    *
    * @method requestTranslationStatus
    * @param {jQuery} id A jQuery object that selects a <div> element.
    */
   app.requestTranslationStatus = function (id) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "translation/Status"},
         dataType: "text",
         success: function (data) {
            id.html("<strong>" + data + "</strong>");
         }
      });
   };

   /**
    * Requests pending tasks for the user.
    *
    * @method requestPendingTasks
    * @param {jQuery} id A jQuery object that selects a <div> element.
    */
   app.requestPendingTasks = function (id) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "task/CountPendingTasks"},
         dataType: "html",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               id.children().last().html(data);
            }
         }
      });
   };

   /**
    * Requests new messages the user has.
    *
    * @method requestNewMessages
    * @param {jQuery} id A jQuery object that selects a <div> element.
    */
   app.requestNewMessages = function (id) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "mail/CountNewMessages"},
         dataType: "html",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               id.children().last().html(data);
            }
         }
      });
   };
   
   /**
    * Requests add-on file info and add that info to download button-
    *
    * @method requestAddonFileInfo
    */
   app.requestAddonFileInfo = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "GetAddonFileInfo"},
         dataType: "text",
         success: function (data) {
            if (data !== null) {
               $("#download-button").attr("title", data).tooltip();
            }
         }
      });
   };
   
   /**
    * User data.
    *
    * @attribute user
    * @type Object
    */
   app.user = {username: null, type: null};
   
   /**
    * Requests the name of the user.
    *
    * @method requestUsername
    * @param {jQuery} id A jQuery object that selects a <div> element.
    */
   app.requestUsername = function (id) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "user/SelectUsername"},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               app.user.username = data.username;
               app.user.type = data.type;
               id.children().last().html(data.username);
            }
         }
      });
   };

   /**
    * Requests an ESO table description modification.
    *
    * @method updateEsoTable
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.updateEsoTable = function (dialogid) {
      var td = dialogid.find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "translation/UpdateEsoTable",
                tablename: {field: td.eq(0).text(),
                            value: dialogid.find("input[name=tablename]").val()},
                description: {field: td.eq(2).text(),
                              value: dialogid.find("input[name=description]").val()},
                type: {field: td.eq(4).text(),
                       value: app.modifyEsoTableDialog.getSelectedType()}
         },
         success: function (data) {
            if (data.result === 'null') {
               dialogid.find("#wrong-data").find("strong").html(data.html);
               dialogid.find("#wrong-data").show();
            } else {
               dialogid.dialog("close");
               app.esoIndexTable.request();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests a new account creation.
    *
    * @method insertUser
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.insertUser = function (dialogid) {
      var td = dialogid.find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "user/InsertUser",
                username: {field: td.eq(0).text(),
                           value: dialogid.find("input[name=username]").val()},
                password: {field: td.eq(2).text(),
                           value: dialogid.find("input[name=password]").val()},
                passwordv: {field: td.eq(4).text(),
                            value: dialogid.find("input[name=passwordv]").val()},
                type: {field: td.eq(6).text(),
                       value: app.newUserDialog.getSelectedType()},
                name: {field: td.eq(8).text(),
                       value: dialogid.find("input[name=name]").val()},
                gender: {field: td.eq(10).text(),
                         value: app.newUserDialog.getSelectedGender()},
                email: {field: td.eq(12).text(),
                        value: dialogid.find("input[name=email]").val()},
                advisor: {field: td.eq(14).text(),
                          value: app.newUserDialog.getSelectedAdvisor()}
         },
         success: function (data) {
            if (data.result === 'null') {
               dialogid.find("#wrong-data").find("strong").html(data.html);
               dialogid.find("#wrong-data").show();
            } else {
               app.showBanner($("#admin-users-info"), data.html);
               dialogid.dialog("close");
               // Update user lists.
               app.userTable.firstPage();
               app.userTable.request();
               app.briefUserTable1.request();
               app.briefUserTable2.request();
               app.briefUserTable3.request();
               app.statsTable.firstPage();
               app.statsTable.request();
               // Update task lists.
               app.userTaskTable.firstPage();
               app.userTaskTable.request();
               app.userTaskTable.removeSelectedTaskId();
               app.assignerTaskTable.firstPage();
               app.assignerTaskTable.request();
               app.assignerTaskTable.removeSelectedTaskId();
               app.adminTaskTable.firstPage();
               app.adminTaskTable.request();
               app.adminTaskTable.removeSelectedTaskId();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests an account modification that is made by an admin.
    *
    * @method updateUser
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.updateUser = function (dialogid) {
      var td = $(dialogid).find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "user/UpdateUser",
                username: {field: td.eq(0).text(),
                           value: $(dialogid).find("input[name=username]").val()},
                password: {field: td.eq(2).text(),
                           value: $(dialogid).find("input[name=password]").val()},
                passwordv: {field: td.eq(4).text(),
                            value: $(dialogid).find("input[name=passwordv]").val()},
                type: {field: td.eq(6).text(),
                       value: app.modifyUserDialog.getSelectedType()},
                name: {field: td.eq(8).text(),
                       value: $(dialogid).find("input[name=name]").val()},
                gender: {field: td.eq(10).text(),
                         value: app.modifyUserDialog.getSelectedGender()},
                email: {field: td.eq(12).text(),
                        value: $(dialogid).find("input[name=email]").val()},
                advisor: {field: td.eq(14).text(),
                          value: app.modifyUserDialog.getSelectedAdvisor()}
         },
         success: function (data) {
            if (data.result === 'null') {
               $(dialogid).find("#wrong-data").find("strong").html(data.html);
               $(dialogid).find("#wrong-data").show();
            } else {
               app.showBanner($("#admin-users-info"), data.html);
               dialogid.dialog("close");
               app.userTable.request();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests an account deletion.
    *
    * @method deleteUser
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.deleteUser = function (dialogid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "user/DeleteUser",
                username: {field: "username",
                           value: app.userTable.getSelectedUsername()}
         },
         success: function (data) {
            if (data.result === 'null') {
               dialogid.find("#wrong-data").find("strong").html(data.html);
               dialogid.find("#wrong-data").show();
            } else {
               app.showBanner($("#admin-users-info"), data.html);
               dialogid.dialog("close");
               // Update user lists.
               app.userTable.firstPage();
               app.userTable.request();
               app.briefUserTable1.request();
               app.briefUserTable2.request();
               app.briefUserTable3.request();
               app.statsTable.firstPage();
               app.statsTable.request();
               // Update task lists.
               app.userTaskTable.firstPage();
               app.userTaskTable.request();
               app.userTaskTable.removeSelectedTaskId();
               app.assignerTaskTable.firstPage();
               app.assignerTaskTable.request();
               app.assignerTaskTable.removeSelectedTaskId();
               app.adminTaskTable.firstPage();
               app.adminTaskTable.request();
               app.adminTaskTable.removeSelectedTaskId();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests an account modification that is made by the current user.
    *
    * @method updateCurrentUser
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.updateCurrentUser = function (dialogid) {
      var td = $(dialogid).find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "user/UpdateCurrentUser",
                username: {field: td.eq(0).text(),
                           value: $(dialogid).find("input[name=username]").val()},
                password: {field: td.eq(2).text(),
                           value: $(dialogid).find("input[name=password]").val()},
                passwordv: {field: td.eq(4).text(),
                            value: $(dialogid).find("input[name=passwordv]").val()},
                name: {field: td.eq(6).text(),
                       value: $(dialogid).find("input[name=name]").val()},
                gender: {field: td.eq(8).text(),
                         value: app.modifyCurrentUserDialog.getSelectedGender()},
                email: {field: td.eq(10).text(),
                        value: $(dialogid).find("input[name=email]").val()},
                theme: {field: td.eq(12).text(),
                        value: app.modifyCurrentUserDialog.getSelectedTheme()}
         },
         success: function (data) {
            if (data.result === 'null') {
               $(dialogid).find("#wrong-data").find("strong").html(data.html);
               $(dialogid).find("#wrong-data").show();
            } else {
               app.showBanner($("#current-user-info"), data.html);
               dialogid.dialog("close");
               // The UI theme has been changed.
               if (data.reload === true) {
                  location.reload();
               } else { 
                  app.userTable.request();
               }
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests the master table to the server. The master table contains an index of the translation
    * tables. The table will be append to the DOM on success.
    *
    * @method listEsoTables
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    */
   app.listEsoTables = function (tableid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "translation/ListEsoTables"},
         dataType: "html",
         success: function (data) {
            // Add button to change table description.
            function addChangeButtons() {
               var row = 0;
               $(tableid).find("tr").each(function () {
                  var tableElement = $(this).children().eq(2); // Column 2: Description.
                  if (row > 0) {
                     tableElement.prepend('<button class="ui-state-default eso-table-edit-button" style="vertical-align: middle;"></button>');
                  } else {
                     row++;
                  }
               });
               
               // Bind the edit button to the dialog that allows to modify the table description.
               $(".eso-table-edit-button").button({icons: {primary: "ui-icon-pencil"}, text: false}).click(function (e) {
                  var columns = [];
                  columns[0] = $(this).parent().prev().prev().text();
                  columns[1] = $(this).parent().prev().text();
                  app.esoIndexTable.setSelectedTable(columns);
                  $("#update-esotable-dialog").dialog("open");
                  e.stopPropagation();
               });
            }
            
            // Create the table.
            tableid.html(data).rtable(app.getLocalization().getTable(), app.esoIndexTable.getSelection(), undefined, app.esoIndexTable.getHiddenColumns(), addChangeButtons);
         }
      });
   };

   /**
    * Requests Rocinante's users to the server. The table will be append to the DOM on success.
    *
    * @method listUsers
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {Integer} column Index of the column used to sort the table.
    * @param {Boolean} asc Defines whether sorting is ascending or not.
    */
   app.listUsers = function (tableid, column, asc) {
      var page = app.userTable.getPage() || 1;
      
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "user/ListUsers", rpp: app.userTable.getRowsPerPage(), page: page, column: column, asc: asc},
         dataType: "json",
         success: function (data) {
            // Restore initial state of buttons "Modify user", and "Delete user".
            app.userTable.getSelection().onUnselect();
            // Set data for pager.
            app.userTable.setPage(data.page);
            app.userTable.setTotalPages(data.total);
            // Show table.
            tableid.html(data.html).rtable(app.getLocalization().getTable(), 
                                           app.userTable.getSelection(),
                                           app.userTable.getPager(),
                                           app.userTable.getHiddenColumns(),
                                           function () {},
                                           function (column) {
                                                    app.userTable.setSorting(column);
                                                    app.userTable.request();
                                           });
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests a language table (lua, meta, or lang) to the server. The table will be append to the
    * DOM on success.
    *
    * @method listLangTable
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {String} table Table identifier in the database.
    */
   app.listLangTable = function (tableid, table) {
      var page = app.langTables[tableid].getPage() || 1;
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "translation/ListLangTable", tableid: table, rpp: app.langTables[tableid].getRowsPerPage(), page: page},
         dataType: "json",
         success: function (data) {
            // Set data for pager.
            app.langTables[tableid].setPage(data.page);
            app.langTables[tableid].setTotalPages(data.total);
            // Show table.
            $('#' + tableid).html(data.html).langtable(app, app.langTables[tableid].getPager());
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a language table (lua, meta, or lang) to the server that is bind to a task. The table
    * will be append to the DOM on success.
    *
    * @method listTaskTable
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {String} task A task ID.
    */
   app.listTaskTable = function (tableid, task) {
      var page = app.taskTables[tableid].getPage() || 1;
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "task/ListTaskTable", taskid: task, rpp: app.taskTables[tableid].getRowsPerPage(), page: page},
         dataType: "json",
         success: function (data) {
            // Set data for pager.
            app.taskTables[tableid].setPage(data.page);
            app.taskTables[tableid].setTotalPages(data.total);
            // Show table.
            $('#' + tableid).html(data.html).langtable(app, app.taskTables[tableid].getPager(), data.typeOfTask);
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests terms of the glossary that are included in a given English string. Terms and their
    * translations will be append to the DOM on success.
    *
    * @method listGlossaryForText
    * @param {jQuery} tdid A jQuery object that selects a <td> element.
    * @param {String} tableid A ESO table ID.
    * @param {String} textid A string ID.
    * @param {String{ seqid A sequence ID.
    */
   app.listGlossaryForText = function (tdid, tableid, textid, seqid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "translation/SelectLangGlossary", tableid: tableid, textid: textid, seqid: seqid},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               // Show glossary terms and attach click event to them. Locked terms can't be changed unless user is ADVISOR, or ADMIN.
               var html = '';
               var canAddTerms = data.terms[data.count - 1].term === '+';
               var lastTerm = canAddTerms ? data.count - 1 : data.count;
               for (var i = 0; i < lastTerm; i++) {
                  html += '<div class="ui-widget-header original-token ' + data.terms[i].css + '" style="cursor: ' +
                          (data.locks[data.terms[i].term] !== true ? 'pointer' : 'auto') +
                          '"><span class="term-note" title="' + data.terms[i].note + '">' + data.terms[i].term + '</span>' +
                          '<div class="ui-widget-content translated-token" style="cursor: auto">' + data.terms[i].translation +
                          '</div><div style="display: none">' + data.terms[i].termid +
                          '</div></div>\n';
               }
               if (canAddTerms) {
                  html += '<div class="ui-widget-header original-token" id="add-token" style="cursor: pointer">' + data.terms[data.count - 1].term +
                          '<div class="ui-widget-content translated-token" id="add-token" style="cursor: pointer">' + data.terms[data.count - 1].translation +
                          '</div></div>\n';
               }
               tdid.html(html);
               
               $(".term-note").each(function () {
                  $(this).tooltip();
               });
               $("#add-token").click(function () {
                  app.newTermDialog.setTranslation({td: tdid, tableid: tableid, textid: textid, seqid: seqid});
                  app.bindDialog($(this), $("#new-term-dialog"));
               });
               $(".original-token").not("#add-token").click(function () {
                  if (data.locks[$(this).contents().eq(0).text()] !== true) {
                     app.updateTermDialog.setTranslation({td: tdid, tableid: tableid, textid: textid, seqid: seqid});
                     app.updateTermDialog.setTerm($(this).contents().eq(0).text());
                     app.updateTermDialog.setTermId($(this).contents().eq(2).text());
                     app.bindDialog($(this), $("#update-term-dialog"));
                  }
               });
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests an update of a translation string and its status.
    *
    * @method modifyLang
    * @param {Object} data An object with these atributes: tableid, textid, seqid, text (translation),
    * isEditing ,isTranslated, isRevised, isLocked, and isDisputed.
    */
   app.updateLang = function (data) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "translation/UpdateLang",
                tableid: {field: "TableId", value: data.tableid},
                textid: {field: "TextId", value: data.textid},
                seqid: {field: "SeqId", value: data.seqid},
                text: {field: "Es", value: data.text},
                isUpdated: {field: "IsUpdated", value: data.isUpdated},
                isTranslated: {field: "IsTranslated", value: data.isTranslated},
                isRevised: {field: "IsRevised", value: data.isRevised},
                isLocked: {field: "IsLocked", value: data.isLocked},
                isDisputed: {field: "IsDisputed", value: data.isDisputed}
         },
         success: function (result) {
            if (result.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               // Show string as it's stored in database.
               if (result !== "OK") {
                  data.selector.html(result);
               }
               app.userTaskTable.firstPage();
               app.userTaskTable.request();
               app.userTaskTable.removeSelectedTaskId();
               app.assignerTaskTable.firstPage();
               app.assignerTaskTable.request();
               app.assignerTaskTable.removeSelectedTaskId();
               app.adminTaskTable.firstPage();
               app.adminTaskTable.request();
               app.adminTaskTable.removeSelectedTaskId();
               app.esoIndexTable.request();
               app.requestTranslationStatus($("#translation-status"));
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests an update of a translation comment.
    *
    * @method modifyLang
    * @param {Object} data An object with these atributes: tableid, textid, seqid, and text (note).
    */
   app.updateLangNotes = function (data) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "translation/UpdateLangNotes",
                tableid: {field: "TableId", value: data.tableid},
                textid: {field: "TextId", value: data.textid},
                seqid: {field: "SeqId", value: data.seqid},
                text: {field: "Notes", value: data.text}
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests an insertion of a new glossary term.
    *
    * @method insertTerm
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Object} translation An object whose properties are td, tableid, textid, and seqid.
    */
   app.insertTerm = function (dialogid, translation) {
      var td = dialogid.find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "translation/InsertTerm",
                term: {field: td.eq(0).text(),
                       value: dialogid.find("input[name=term]").val()},
                plural: {field: td.eq(2).text(),
                              value: dialogid.find("input[name=plural]").val()},
                translation: {field: td.eq(4).text(),
                              value: dialogid.find("input[name=translation]").val()},
                typeid: {field: td.eq(6).text(),
                         value: app.newTermDialog.getSelectedTermType()},
                note: {field: td.eq(8).text(),
                       value: dialogid.find("textarea[name=note]").val()}                    
         },
         success: function (data) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            if (data.result === 'ok') {
               dialogid.dialog("close");
               app.listGlossaryForText(translation.td, translation.tableid, translation.textid, translation.seqid);
            } else {
               dialogid.find("#wrong-data").find("strong").html(data.html);
               dialogid.find("#wrong-data").show();
            }
         },
         error: function (e, xhr) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            console.log(xhr);
         }
      });
   };

   /**
    * Requests an update of a glossary term.
    *
    * @method updateTerm
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Object} translation An object whose properties are td, tableid, textid, and seqid.
    */
   app.updateTerm = function (dialogid, translation) {
      var td = dialogid.find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "translation/UpdateTerm",
                termid: {field: td.eq(0).text(),
                         value: dialogid.find("input[name=termid]").val()},
                term: {field: td.eq(2).text(),
                       value: dialogid.find("input[name=term]").val()},
                plural: {field: td.eq(4).text(),
                              value: dialogid.find("input[name=plural]").val()},
                translation: {field: td.eq(6).text(),
                              value: dialogid.find("input[name=translation]").val()},
                typeid: {field: td.eq(8).text(),
                         value: app.updateTermDialog.getSelectedTermType()},
                note: {field: td.eq(10).text(),
                       value: dialogid.find("textarea[name=note]").val()},
                locked: {field: td.eq(12).text(),
                         value: app.updateTermDialog.getSelectedLockedTerm()}
         },
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               dialogid.parent().find("#button-ok").attr("disabled", false);
               dialogid.find("#dialog-ajax-loader").hide();
               dialogid.find("#delete-term").attr("disabled", false);
               if (data.result === 'ok') {
                  dialogid.dialog("close");
                  app.listGlossaryForText(translation.td, translation.tableid, translation.textid, translation.seqid);
                  app.userTaskTable.firstPage();
                  app.userTaskTable.request();
                  app.userTaskTable.removeSelectedTaskId();
                  app.requestPendingTasks($("#task-button"));
               }
            }
         },
         error: function (e, xhr) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            dialogid.find("#delete-term").attr("disabled", false);
            console.log(xhr);
         }
      });
   };

   /**
    * Requests a deletion of a glossary term.
    *
    * @method deleteTerm
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.deleteTerm = function (dialogid) {
      var td = dialogid.find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "translation/DeleteTerm",
                termid: {field: td.eq(0).text(),
                         value: dialogid.find("input[name=termid]").val()}
         },
         success: function (data) {
            if (data.result === 'ok') {
               var translation = app.updateTermDialog.getTranslation();
               dialogid.dialog("close");
               app.listGlossaryForText(translation.td, translation.tableid, translation.textid, translation.seqid);
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   
   /**
    * Requests how many times a given term is in the game text.
    *
    * @method deleteTerm
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {Boolean} isPlural Whether the term is plural or not.
    */
   app.countTerm = function (dialogid, isPlural) {
      isPlural = isPlural || false;
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "translation/CountTerm", term: dialogid.find(isPlural ? "input[name=plural]" : "input[name=term]").val()},
         success: function (data) {
            dialogid.find("#counter-info").find("strong").html(data.html);
            dialogid.find("#counter-info").show();
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests task list for the current user. The table will be append to the DOM on success.
    *
    * @method listUserTasks
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {Integer} column Index of the column used to sort the table.
    * @param {Boolean} asc Defines whether sorting is ascending or not.
    */
   app.listUserTasks = function (tableid, column, asc) {
      var page = app.userTaskTable.getPage() || 1;
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "task/ListUserTasks", rpp: app.userTaskTable.getRowsPerPage(), page: page, column: column, asc: asc},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               // Restore initial state of buttons "Modify task", "Delete task", and "Finish task."
               app.userTaskTable.getSelection().onUnselect();
               if (data.count > 0) {
                  // Set data for pager.
                  app.userTaskTable.setPage(data.page);
                  app.userTaskTable.setTotalPages(data.total);
                  // Show table.
                  tableid.html(data.html).rtable(app.getLocalization().getTable(),
                                                 app.userTaskTable.getSelection(), 
                                                 app.userTaskTable.getPager(), 
                                                 app.userTaskTable.getHiddenColumns(),
                                                 function () {},
                                                 function (column) {
                                                   app.userTaskTable.setSorting(column);
                                                   app.userTaskTable.request();
                                                 },
                                                 "userTaskPager");
               } else {
                  $("#userTaskPager").remove();
                  tableid.html(data.html);
               }
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests task list assigned by the current user. The table will be append to the DOM on success.
    *
    * @method listAssignerTasks
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {Integer} column Index of the column used to sort the table.
    * @param {Boolean} asc Defines whether sorting is ascending or not.
    */
   app.listAssignerTasks = function (tableid, column, asc) {
      var page = app.assignerTaskTable.getPage() || 1;
      
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "task/ListAssignerTasks", rpp: app.assignerTaskTable.getRowsPerPage(), page: page, column: column, asc: asc},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.count !== -1) {
               // Restore initial state of buttons "Modify task", "Delete task", and "Finish task."
               app.assignerTaskTable.getSelection().onUnselect();
               // Set data for pager.
               app.assignerTaskTable.setPage(data.page);
               app.assignerTaskTable.setTotalPages(data.total);
               if (data.count > 0) {
                  // Show table.
                  tableid.html(data.html).rtable(app.getLocalization().getTable(), 
                                                 app.assignerTaskTable.getSelection(), 
                                                 app.assignerTaskTable.getPager(), 
                                                 app.assignerTaskTable.getHiddenColumns(),
                                                 function () {},
                                                 function (column) {
                                                    app.assignerTaskTable.setSorting(column);
                                                    app.assignerTaskTable.request();
                                                 },
                                                 "assignerTaskPager");
               } else {
                  $("#assignerTaskPager").remove();
                  tableid.html(data.html);
               }
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests the task list. The table will be append to the DOM on success.
    *
    * @method listTasks
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {Integer} column Index of the column used to sort the table.
    * @param {Boolean} asc Defines whether sorting is ascending or not.
    */
   app.listTasks = function (tableid, column, asc) {
      var page = app.adminTaskTable.getPage() || 1;
      
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "task/ListTasks", rpp: app.adminTaskTable.getRowsPerPage(), page: page, column: column, asc: asc},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.count !== -1) {
               // Restore initial state of buttons "Modify task", "Delete task", and "Finish task."
               app.adminTaskTable.getSelection().onUnselect();
               // Set data for pager.
               app.adminTaskTable.setPage(data.page);
               app.adminTaskTable.setTotalPages(data.total);
               if (data.count > 0) {
                  // Show table.
                  tableid.html(data.html).rtable(app.getLocalization().getTable(), 
                                                 app.adminTaskTable.getSelection(), 
                                                 app.adminTaskTable.getPager(), 
                                                 app.adminTaskTable.getHiddenColumns(),
                                                 function () {},
                                                 function (column) {
                                                    app.adminTaskTable.setSorting(column);
                                                    app.adminTaskTable.request();
                                                 },
                                                 "adminTaskPager");
               } else {
                  $("#adminTaskPager").remove();
                  tableid.html(data.html);
               }
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
      
   /**
    * Requests ESO tables to the server in order to be selected by the user. The table will be 
    * append to the DOM on success.
    *
    * @method listBriefEsoTable
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    */
   app.listBriefEsoTable = function (tableid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "task/ListBriefEsoTables"},
         dataType: "json",
         success: function (data) {
            // Restore initial state of buttons.
            app.briefEsoIndexTable.getSelection().onUnselect();
            // Show table.
            tableid.html(data.html).rtable(app.getLocalization().getTable(), 
                                           app.briefEsoIndexTable.getSelection(), 
                                           undefined, 
                                           app.briefEsoIndexTable.getHiddenColumns());
         }
      });
   };
   
   /**
    * Requests a new task creation.
    *
    * @method insertTask
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.insertTask = function (dialogid) {
      var td = dialogid.find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "task/InsertTask",
                type: {field: td.eq(0).text(),
                       value: app.newTaskDialog.getSelectedTaskType()},
                user: {field: td.eq(2).text(),
                       value: app.newTaskDialog.getSelectedTaskUser()},
                tableid: {field: td.eq(4).text(),
                          value: dialogid.find("input[name=tableid]").val()},
                mode: {field: td.eq(8).text(),
                       value: app.newTaskDialog.getSelectedTaskMode()},
                count: {field: td.eq(12).text(),
                        value: dialogid.find("input[name=count]").val()}
         },
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else {
               dialogid.parent().find("#button-ok").attr("disabled", false);
               dialogid.find("#dialog-ajax-loader").hide();
               if (data.result === 'null') {
                  dialogid.find("#wrong-data").find("strong").html(data.html);
                  dialogid.find("#wrong-data").show();
               } else {
                  app.showBanner($("#user-task-info"), data.html);
                  dialogid.dialog("close");
                  app.userTaskTable.firstPage();
                  app.userTaskTable.request();
                  app.userTaskTable.removeSelectedTaskId();
                  app.assignerTaskTable.firstPage();
                  app.assignerTaskTable.request();
                  app.assignerTaskTable.removeSelectedTaskId();
                  app.adminTaskTable.firstPage();
                  app.adminTaskTable.request();
                  app.adminTaskTable.removeSelectedTaskId();
                  app.esoIndexTable.request();
                  app.requestPendingTasks($("#task-button"));
               }
            }
         },
         error: function (e, xhr) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a task re-assignation.
    *
    * @method reassignTask
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {UpdateTaskDialog} updateTaskDialog Dialog where data will be taken.
    */
   app.reassignTask = function (dialogid, updateTaskDialog) {
      var td = dialogid.find("td");
      dialogid.parent().find("#button-ok").attr("disabled", true);
      dialogid.find("#dialog-ajax-loader").show();
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "task/ReassignTask",
                userid: {field: td.eq(0).text(),
                         value: updateTaskDialog.getSelectedTaskUser()},
                taskid: {field: td.eq(2).text(),
                         value: dialogid.find("input[name=taskid]").val()}
         },
         success: function (data) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            dialogid.dialog("close");
            app.userTaskTable.request();
            app.userTaskTable.removeSelectedTaskId();
            app.assignerTaskTable.request();
            app.assignerTaskTable.removeSelectedTaskId();
            app.adminTaskTable.request();
            app.adminTaskTable.removeSelectedTaskId();
            app.esoIndexTable.request();
            app.requestPendingTasks($("#task-button"));
         },
         error: function (e, xhr) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a revision task creation from a translation task.
    *
    * @method reviseTask
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {UpdateTaskDialog} updateTaskDialog Dialog where data will be taken.
    */
   app.reviseTask = function (dialogid, updateTaskDialog) {
      var td = dialogid.find("td");
      dialogid.parent().find("#button-ok").attr("disabled", true);
      dialogid.find("#dialog-ajax-loader").show();
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "task/ReviseTask",
                userid: {field: td.eq(0).text(),
                         value: updateTaskDialog.getSelectedTaskUser()},
                taskid: {field: td.eq(2).text(),
                         value: dialogid.find("input[name=taskid]").val()}
         },
         success: function (data) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            dialogid.dialog("close");
            app.userTaskTable.firstPage();
            app.userTaskTable.request();
            app.userTaskTable.removeSelectedTaskId();
            app.assignerTaskTable.firstPage();
            app.assignerTaskTable.request();
            app.assignerTaskTable.removeSelectedTaskId();
            app.adminTaskTable.firstPage();
            app.adminTaskTable.request();
            app.adminTaskTable.removeSelectedTaskId();
            app.esoIndexTable.request();
            app.requestPendingTasks($("#task-button"));
         },
         error: function (e, xhr) {
            dialogid.parent().find("#button-ok").attr("disabled", false);
            dialogid.find("#dialog-ajax-loader").hide();
            console.log(xhr);
         }
      });
   };
               
   /**
    * Requests a task deletion.
    *
    * @method deleteTask
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    * @param {DeleteTaskDialog} deleteTaskDialog Dialog where data will be taken.
    */
   app.deleteTask = function (dialogid, deleteTaskDialog) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "task/DeleteTask",
                taskid: deleteTaskDialog.getTaskId()
         },
         success: function (data) {
            if (data.result === 'ok') {
               app.showBanner($("#user-task-info"), data.html);
               dialogid.dialog("close");
               app.userTaskTable.firstPage();
               app.userTaskTable.request();
               app.userTaskTable.removeSelectedTaskId();
               app.assignerTaskTable.firstPage();
               app.assignerTaskTable.request();
               app.assignerTaskTable.removeSelectedTaskId();
               app.adminTaskTable.firstPage();
               app.adminTaskTable.request();
               app.adminTaskTable.removeSelectedTaskId();
               app.esoIndexTable.request();
               app.requestPendingTasks($("#task-button"));
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests inbox messages.
    *
    * @method requestInboxMessages
    * @param {jQuery} id A jQuery object that selects a <div> element.
    */
   app.requestInboxMessages = function (id) {
      var page = app.inboxAccordion.getPage() || 1;
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "mail/ListInbox", rpp: app.inboxAccordion.getRowsPerPage(), page: page},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.count > 0) {
               // Set data for pager.
               app.inboxAccordion.setPage(data.page);
               app.inboxAccordion.setTotalPages(data.total);
               // Show the accordion.
               id.html(data.html).raccordion(app, app.inboxAccordion.getSelection(), app.inboxAccordion.getPager());                  
            } else {
               id.html(data.html);
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests outbox messages.
    *
    * @method requestOutboxMessages
    * @param {jQuery} id A jQuery object that selects a <div> element.
    */
   app.requestOutboxMessages = function (id) {
      var page = app.outboxAccordion.getPage() || 1;
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "mail/ListOutbox", rpp: app.outboxAccordion.getRowsPerPage(), page: page},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.count > 0) {
               // Set data for pager.
               app.outboxAccordion.setPage(data.page);
               app.outboxAccordion.setTotalPages(data.total);
               // Show the accordion.
               id.html(data.html).raccordion(app, app.outboxAccordion.getSelection(), app.outboxAccordion.getPager());                  
            } else {
               id.html(data.html);
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
    
   /**
    * Requests drafts.
    *
    * @method requestDrafts
    * @param {jQuery} id A jQuery object that selects a <div> element.
    */
   app.requestDrafts = function (id) {
      var page = app.draftsAccordion.getPage() || 1;
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "mail/ListDrafts", rpp: app.draftsAccordion.getRowsPerPage(), page: page},
         dataType: "json",
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.count > 0) {
               // Set data for pager.
               app.draftsAccordion.setPage(data.page);
               app.draftsAccordion.setTotalPages(data.total);
               // Show the accordion.
               id.html(data.html).raccordion(app, app.draftsAccordion.getSelection(), app.draftsAccordion.getPager());                  
            } else {
               id.html(data.html);
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a new message creation.
    *
    * @method insertMessage
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.insertMessage = function (dialogid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "mail/InsertMessage",
                addressees: dialogid.find("input[name=addressees]").val(),
                subject: dialogid.find("input[name=subject]").val(),
                body: dialogid.find("textarea[name=body]").val(),
                chatid: dialogid.find("input[name=chatid]").val(),
                isdraft : dialogid.find("input[name=isdraft]").val()
         },
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.result === 'null') {
               dialogid.find("#wrong-data").find("strong").html(data.html);
               dialogid.find("#wrong-data").show();
            } else {
               app.showBanner($("#user-mail-info"), data.html);
               dialogid.dialog("close");
               app.outboxAccordion.firstPage();
               app.outboxAccordion.request();
               app.draftsAccordion.firstPage();
               app.draftsAccordion.request();
               app.requestNewMessages($("#mail-button"));
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a new draft creation.
    *
    * @method insertDraft
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.insertDraft = function (dialogid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "mail/InsertDraft",
                addressees: dialogid.find("input[name=addressees]").val(),
                subject: dialogid.find("input[name=subject]").val(),
                body: dialogid.find("textarea[name=body]").val(),
                chatid: dialogid.find("input[name=chatid]").val(),
                draftid : dialogid.find("input[name=isdraft]").val()
         },
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.result === 'null') {
               dialogid.find("#wrong-data").find("strong").html(data.html);
               dialogid.find("#wrong-data").show();
            } else {
               app.showBanner($("#user-mail-info"), data.html);
               dialogid.dialog("close");
               app.draftsAccordion.firstPage();
               app.draftsAccordion.request();
               app.requestNewMessages($("#mail-button"));
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests the addreessee list to the server in order to be selected by the user. The table will 
    * be append to the DOM on success.
    *
    * @method listBriefUserTable
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {Table} table A table that lists the users.
    */
   app.listBriefUserTable = function (tableid, table) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "user/ListUsernames"},
         dataType: "json",
         success: function (data) {
            // Restore initial state of buttons.
            table.getSelection().onUnselect();
            // Show table.
            tableid.html(data.html).rtable(app.getLocalization().getTable(), 
                                           table.getSelection(), 
                                           undefined,
                                           table.getHiddenColumns());
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   
   /**
    * Marks a given message as read.
    * @param {Integer} mailid A message ID.
    */
   app.markAsRead = function (mailid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "mail/MarkAsRead", mailid: mailid},
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.result === 'ok') {
               app.requestNewMessages($("#mail-button"));
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });   
   };

   /**
    * Requests a message deletion.
    *
    * @method deleteTask
    * @param {Integer} mailid A message ID.
    */
   app.deleteMessage = function (mailid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "mail/DeleteMessage", mailid: mailid},
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            } else if (data.result === 'ok') {
               app.inboxAccordion.request();
               app.outboxAccordion.request();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      }); 
   };
   
   /**
    * Requests a draft deletion.
    *
    * @method deleteDraft
    * @param {Integer} draftid A draft ID.
    */
   app.deleteDraft = function (draftid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "mail/DeleteDraft", draftid: draftid},
         success: function (data) {
            if (data.result === 'ok') {
               app.draftsAccordion.request();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      }); 
   };
   
   /**
    * Request add-on generation.
    *
    * @method generateAddon
    * @param {String} mode NO_EXTRAFILES, ADD_EXTRAFILES, or DELETE_EXTRAFILES.
    * @param {FileList} extraFilesArchive A ZIP archive with additional files that will be included 
    * into the add-on.
    * @param {FileList} clientHeaderFile A text plain file with the header of xx_client.str.
    * @param {FileList} pregameHeaderFile A text plain file with the header of xx_pregame.str.
    * @param {String} version String to identity this version of the add-on.
    */
   app.generateAddon = function (mode, extraFilesArchive, clientHeaderFile, pregameHeaderFile, version) {
      // Create a formdata object and add the command name, the files, and the version number.
      var data = new FormData();
      data.append('cmd', 'addon/GenerateAddon');
      data.append('mode', mode);
      data.append('version', version);
      data.append(0, extraFilesArchive[0]);
      data.append(1, clientHeaderFile[0]);
      data.append(2, pregameHeaderFile[0]);
      
      $("#generate-addon").hide();
      $("#creating-addon").show();
      
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         cache: false,
         processData: false, // Don't process the file.
         contentType: false, // Set content type to false as jQuery will tell the server its a query string request.
         data: data,
         timeout: 0,
         success: function (data) {
            if (data.result === 'ok') {
               window.location.href = 'download.php';
            } else {
               app.showBanner($("#admin-addon-info"), data.html);
            }
            $("#generate-addon").show();
            $("#creating-addon").hide();
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      }); 
   };
   
   /**
    * Requests to Start database update. The first step is generating SQL queries as a result of 
    * comparing old strings to new ones.
    *
    * @method startDatabaseUpdate
    */
   app.startDatabaseUpdate = function () {
      // Create a formdata object and add the command name, the files, and the version number.
      var data = new FormData();
      data.append('cmd', 'update/DumpFiles');
      data.append(0, app.UpdateDatabasePanel.getSelectedFrClientFile());
      data.append(1, app.UpdateDatabasePanel.getSelectedFrPregameFile());
      data.append(2, app.UpdateDatabasePanel.getSelectedFrLangFile());
      data.append(3, app.UpdateDatabasePanel.getSelectedEnClientFile());
      data.append(4, app.UpdateDatabasePanel.getSelectedEnPregameFile());
      data.append(5, app.UpdateDatabasePanel.getSelectedEnLangFile());
      
      $("#update-database").hide();
      $("#updating-database").show();
      
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         cache: false,
         processData: false, // Don't process the file.
         contentType: false, // Set content type to false as jQuery will tell the server its a query string request.
         data: data,
         timeout: 0,
         success: function (data) {
            if (data.result === 'ok') {
               app.updateDatabase();
            } else {
               $("#update-database").show();
               $("#updating-database").hide();
               app.showBanner($("#admin-update-info"), data.html);
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      }); 
   };
   
   /**
    * Request database update. The process will start only if file dumping is complete.
    * 
    * @method updateDatabase
    */
   app.updateDatabase = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "update/UpdateDatabase"},
         dataType: "json",
         timeout: 0,
         success: function (data) {
            if (data.result === 'ok') {
               location.reload();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests Rocinante's stats to the server. The table will be append to the DOM on success.
    *
    * @method listStats
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {Integer} column Index of the column used to sort the table.
    * @param {Boolean} asc Defines whether sorting is ascending or not.
    */
   app.listStats = function (tableid, column, asc) {
      var page = app.statsTable.getPage() || 1;
      
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "user/ListStats", rpp: app.statsTable.getRowsPerPage(), page: page, column: column, asc: asc},
         dataType: "json",
         success: function (data) {
            // Set data for pager.
            app.statsTable.setPage(data.page);
            app.statsTable.setTotalPages(data.total);
            // Show table.
            tableid.html(data.html).rtable(app.getLocalization().getTable(), 
                                           app.statsTable.getSelection(), 
                                           app.statsTable.getPager(),
                                           app.statsTable.getHiddenColumns(),
                                           function () {},
                                           function (column) {
                                              app.statsTable.setSorting(column);
                                              app.statsTable.request();
                                           });
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a maintenance mode update.
    *
    * @method updateMaintenanceMode
    * @param {jQuery} dialogid A jQuery object that selects a <div> element.
    */
   app.updateMaintenanceMode = function (dialogid) {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "UpdateMaintenanceMode", 
                status: app.maintenanceModeDialog.getSelectedMode(),
                message: dialogid.find("textarea[name=message]").val()
         },
         success: function (data) {
            if (data.status === 'ON') {
               $("#maintenance-mode-status").show();
            } else {
               $("#maintenance-mode-status").hide();
            }
            
            if (data.message !== null && data.message.length > 0) {
               $("#important-info").find("strong").html(data.message);
               $("#important-info").show();
            } else {
               $("#important-info").hide();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Checks whether maintenance mode is active.
    *
    * @method checkMaintenanceMode
    */
   app.checkMaintenanceMode = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "SelectMaintenanceMode"},
         success: function (data) {
            if (data.status === 'ON') {
               $("#maintenance-mode-status").show();
            } else {
               $("#maintenance-mode-status").hide();
            }
            
            if (data.message !== null && data.message.length > 0) {
               $("#important-info").find("strong").html(data.message);
               $("#important-info").show();
            } else {
               $("#important-info").hide();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Checks whether there are modified strings. When there is some modified string, translators 
    * can create a new updating task.
    *
    * @method checkForUpdatingTasks
    */
   app.checkModifiedStrings = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "task/CheckModifiedStrings"},
         success: function (data) {
            app.areUpdatingTasksAvailable = data.result;
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a search for a given text. 
    * 
    * @method search
    * @param {jQuery} tableid A jQuery object that selects a <table> element.
    * @param {String} text A string to search.
    * @param {String} prefix Lang, or Lua.
    */
   app.search = function (tableid, text, prefix) {
      var searchTable = (prefix === "Lang" ? app.searchLangTable : app.searchLuaTable);
      var page = searchTable.getPage() || 1;
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "translation/Search", table: prefix, text: text, rpp: searchTable.getRowsPerPage(), page: page},
         dataType: "json",
         success: function (data) {
            if (data.total !== 0) {
               // Set data for pager.
               searchTable.setPage(data.page);
               searchTable.setTotalPages(data.total);
               // Show table.
               $('#' + tableid).html(data.html).langtable(app, searchTable.getPager());
            } else {
               $('#' + tableid).html(data.html);
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests for reseting session timeout.
    */
   app.refreshSession = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "RefreshSession"},
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET') {
               location.reload();
            }
            else if (data.result === 'fail') {
               app.logout();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });  
   };
   
   /**
    * Requests a destruction of the current session.
    *
    * @method logout
    */
   app.logout = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "login/Logout"},
         success: function (data) {
            if (data.result === 'SESSION_IS_NOT_SET' || data.result === 'ok') {
               location.reload();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      }); 
   };
};

Rocinante.modules.init = function (app) {
   
   /**
    * Hides everything and creates main tabs.
    * 
    * @method start
    */
   app.start = function () {
      app.hideWidgets();
      app.createTabs($("#main-tabs"));
   };
   
   /**
    * Gets the language that Rocinante speaks.
    *
    * @method localize
    */
   app.localize = function () {
      app.requestLocalization();
      app.requestTranslationButtonSet();
   };
      
   /**
    * Requests main information to the server and sets up UI widgets that are displayed in the
    * frontpage.
    * 
    * @method setupFrontPage
    */
   app.setupFrontPage = function () {
      // Request initial information.
      app.checkMaintenanceMode();
      app.checkModifiedStrings();
      app.requestTranslationStatus($("#translation-status"));
      app.requestPendingTasks($("#task-button"));
      app.requestNewMessages($("#mail-button"));
      app.requestUsername($("#user-button"));
      app.requestAddonFileInfo();

      app.esoIndexTable.request();
      app.createUpdateEsoTableDialog($("#update-esotable-dialog"), 550);

      app.createButton($("#download-button"), "ui-icon-circle-arrow-s");
      app.createButton($("#logout-button"), "ui-icon-power");
      app.createButton($("#mail-button"), "ui-icon-mail-closed");
      app.createButton($("#task-button"), "ui-icon-note");
      app.createButton($("#user-button"), "ui-icon-person");
   };
   
   /**
    * Creates a dialog that allows an user to modify his account information.
    *
    * @method setupUpdateCurrentUserDialog
    */
   app.setupUpdateCurrentUserDialog = function () {
      app.createUpdateCurrentUserDialog($("#update-current-user-dialog"), 500);
      app.bindDialog($("#user-button"), $("#update-current-user-dialog"));
   };
      
   /**
    * Sets up glossary and ESO code dialogs.
    * 
    * @method setupTranslationHelpers
    */
   app.setupTranslationHelpers = function () {
      app.createEsoCodeDialog($("#eso-code-dialog"), 700);
      app.createNewTermDialog($("#new-term-dialog"), 700);
      app.createUpdateTermDialog($("#update-term-dialog"), 790);
      app.createButton($("#delete-term"), "ui-icon-closethick");
      app.createButton($("#count-new-singular-term"), "ui-icon-calculator");
      app.createButton($("#count-new-plural-term"), "ui-icon-calculator");
      app.createButton($("#count-modify-singular-term"), "ui-icon-calculator");
      app.createButton($("#count-modify-plural-term"), "ui-icon-calculator");
   };
   
   /**
    * Sets up a tab that allows an user to manage his tasks.
    * 
    * @method setupTaskTab
    */
   app.setupTaskTab = function () {
      app.userTaskTable.request();
      app.assignerTaskTable.request();
      app.createButton($("#new-task-button"), "ui-icon-newwin");
      app.createButton($("#open-task-button"), "ui-icon-newwin");
      app.createButton($("#reassign-task-button"), "ui-icon-newwin");
      app.createButton($("#revise-task-button"), "ui-icon-newwin");
      app.createButton($("#delete-task-button"), "ui-icon-newwin");
   };
   
   /**
    * Sets up a dialog that allows an user to create a new task.
    * 
    * @method setupNewTaskDialog
    */
   app.setupNewTaskDialog = function () {
      // Create new task dialog.
      app.createNewTaskDialog($("#new-task-dialog"), 560);
      app.bindDialog($("#new-task-button"), $("#new-task-dialog"));
      app.createSelectEsoTableDialog($("#select-esotable-dialog"), 630);
      app.createButton($("#search-task-table"), "ui-icon-newwin");
      app.bindDialog($("#search-task-table"), $("#select-esotable-dialog"));
      app.briefEsoIndexTable.request();
   };
   
   /**
    * Sets up a dialog that allows an advisor to assign an existing task to another user.
    * 
    * @method setupReassignTaskDialog
    */
   app.setupReassignTaskDialog = function () {
      // Create reassign task dialog.
      app.reassignTaskDialog = app.createModifyTaskDialog($("#reassign-task-dialog"), 550, app.assignerTaskTable, "task/SelectPupils", function () {
         app.reassignTask($("#reassign-task-dialog"), app.reassignTaskDialog);
      });
      app.bindDialog($("#reassign-task-button"), $("#reassign-task-dialog"));
   };
    
   /**
    * Sets up a dialog that allows an user to revise a task, i.e. to create a new revision task from
    * an existing translation task.
    * 
    * @method setupReviseTaskDialog
    */
   app.setupReviseTaskDialog = function () {
      // Create revise task dialog.
      app.reviseTaskDialog = app.createModifyTaskDialog($("#revise-task-dialog"), 550, app.assignerTaskTable, "task/SelectPupils", function () {
         app.reviseTask($("#revise-task-dialog"), app.reviseTaskDialog);
      });
      app.bindDialog($("#revise-task-button"), $("#revise-task-dialog"));
   };
   
   /**
    * Sets up a dialog that allows an user to delete a task.
    * 
    * @method setupDeleteTaskDialog
    */
   app.setupDeleteTaskDialog = function () {
      app.deleteTaskDialog = app.createDeleteTaskDialog($("#delete-task-dialog"), 550,
         function () {
            var taskid = app.userTaskTable.getSelectedTaskId();
            if (!taskid) {
               taskid = app.assignerTaskTable.getSelectedTaskId();
            }
            return taskid;
         }, 
         function () {
            app.deleteTask($("#delete-task-dialog"), app.deleteTaskDialog);
         }
      );
      app.bindDialog($("#delete-task-button"), $("#delete-task-dialog"));
   };
   
   /**
    * Sets up a tab that allows an user to manage his internal mail.
    * 
    * @method setupMailTab
    */
   app.setupMailTab = function () {
      app.createTabs($("#mail-tabs"));
      app.createButton($("#new-message-button"), "ui-icon-newwin");
      app.createButton($("#new-reply-button"), "ui-icon-newwin");
      app.createButton($("#new-reply-all-button"), "ui-icon-newwin");
      app.createButton($("#open-draft-button"), "ui-icon-newwin");
      app.createButton($("#delete-message-button"), "ui-icon-newwin");
      app.inboxAccordion.request();
      app.outboxAccordion.request();
      app.draftsAccordion.request();
   };
   
   /**
    * Sets up a dialog that allows an user to write a message for another user or other users.
    * 
    * @method setupNewMessageDialog
    */
   app.setupNewMessageDialog = function () {
      // Create new message dialog.
      app.createNewMessageDialog($("#new-message-dialog"), 700);
      app.bindDialog($("#new-message-button"), $("#new-message-dialog"));
      app.selectUserDialog1 = app.createSelectUserDialog($("#select-user-dialog-1"), 400, $("#new-message-dialog"), app.briefUserTable1);
      app.createButton($("#search-user-table-1"), "ui-icon-newwin");
      app.bindDialog($("#search-user-table-1"), $("#select-user-dialog-1"));
      app.briefUserTable1.request();
   };
   
   /**
    * Sets up a dialog that allows an user to reply a message that was sent by another user.
    * 
    * @method setupNewReplyDialog
    */
   app.setupNewReplyDialog = function () {
      // Create new reply dialog.
      app.createNewReplyDialog($("#new-reply-dialog"), 700);
      app.bindReplyDialog($("#new-reply-button"), $("#new-reply-dialog"), 0);
      app.bindReplyDialog($("#new-reply-all-button"), $("#new-reply-dialog"), 1);
      app.selectUserDialog2 = app.createSelectUserDialog($("#select-user-dialog-2"), 400, $("#new-reply-dialog"), app.briefUserTable2);
      app.createButton($("#search-user-table-2"), "ui-icon-newwin");
      app.bindDialog($("#search-user-table-2"), $("#select-user-dialog-2"));
      app.briefUserTable2.request();
   };
   
   /**
    * Sets up a dialog that allows an user to send a message from a draft.
    * 
    * @method setupNewMessageFromDraftDialog
    */
   app.setupNewMessageFromDraftDialog = function () {
      // Create new message from draft dialog.
      app.createNewMessageFromDraftDialog($("#new-message-from-draft-dialog"), 700);
      app.bindDialog($("#open-draft-button"), $("#new-message-from-draft-dialog"));
      app.selectUserDialog3 = app.createSelectUserDialog($("#select-user-dialog-3"), 400, $("#new-message-from-draft-dialog"), app.briefUserTable3);
      app.createButton($("#search-user-table-3"), "ui-icon-newwin");
      app.bindDialog($("#search-user-table-3"), $("#select-user-dialog-3"));
      app.briefUserTable3.request();     
   };
   
   /**
    * Sets up a tab that allows an admin to see statistics about user activity.
    * 
    * @method setupAdminStatsTab
    */
   app.setupAdminStatsTab = function () {
      app.statsTable.request();
   };
   
   /**
    * Sets up a tab that allows an admin to manage all the existing user accounts.
    * 
    * @method setupAdminUserManagementTab
    */
   app.setupAdminUserManagementTab = function () {
      app.createTabs($("#admin-tabs"));
      app.createButton($("#new-user-button"), "ui-icon-newwin");
      app.createButton($("#modify-user-button"), "ui-icon-newwin");
      app.createButton($("#delete-user-button"), "ui-icon-newwin");
      app.userTable.request();
      // Create new user dialog.
      app.createNewUserDialog($("#new-user-dialog"), 500);
      app.bindDialog($("#new-user-button"), $("#new-user-dialog"));
      // Create modify user dialog.
      app.createUpdateUserDialog($("#update-user-dialog"), 500);
      app.bindDialog($("#modify-user-button"), $("#update-user-dialog"));
      // Create delete user dialog.
      app.createDeleteUserDialog($("#delete-user-dialog"), 500);
      app.bindDialog($("#delete-user-button"), $("#delete-user-dialog"));
   };
   
   /**
    * Sets up a tab that allows an admin to manage all the existing tasks.
    * 
    * @method setupAdminTaskManagementTab
    */
   app.setupAdminTaskManagementTab = function () {
      app.adminTaskTable.request();
      app.createButton($("#new-task-admin-button"), "ui-icon-newwin");
      app.createButton($("#open-task-admin-button"), "ui-icon-newwin");
      app.createButton($("#reassign-task-admin-button"), "ui-icon-newwin");
      app.createButton($("#revise-task-admin-button"), "ui-icon-newwin");
      app.createButton($("#delete-task-admin-button"), "ui-icon-newwin");
      // New task dialog.
      app.bindDialog($("#new-task-admin-button"), $("#new-task-dialog"));
      // Create reassign task dialog.
      app.reassignTaskAdminDialog = app.createModifyTaskDialog($("#reassign-task-admin-dialog"), 550, app.adminTaskTable, "task/SelectUsernames", function () {
         app.reassignTask($("#reassign-task-admin-dialog"), app.reassignTaskAdminDialog);
      });
      app.bindDialog($("#reassign-task-admin-button"), $("#reassign-task-admin-dialog"));
      // Create revise task dialog.
      app.reviseTaskAdminDialog = app.createModifyTaskDialog($("#revise-task-admin-dialog"), 550, app.adminTaskTable, "task/SelectUsernames", function () {
         app.reviseTask($("#revise-task-admin-dialog"), app.reviseTaskAdminDialog);
      });
      app.bindDialog($("#revise-task-admin-button"), $("#revise-task-admin-dialog"));
      // Create delete task dialog.
      app.deleteTaskAdminDialog = app.createDeleteTaskDialog($("#delete-task-admin-dialog"), 550,
         function () {
            return app.adminTaskTable.getSelectedTaskId();
         }, 
         function () {
            app.deleteTask($("#delete-task-admin-dialog"), app.deleteTaskAdminDialog);
         }
      );
      // Delete task dialog.
      app.bindDialog($("#delete-task-admin-button"), $("#delete-task-admin-dialog"));
   };
   
   /**
    * Sets up a tab that allows an admin to build a translation add-on for The Elder Scrolls Online®.
    * 
    * @method setupAdminUpdateDatabaseTab
    */
   app.setupAdminAddonTab = function () {
      app.createButton($("#select-extra-files"), "ui-icon-folder-open");
      app.createButton($("#select-clientheader-file"), "ui-icon-folder-open");
      app.createButton($("#select-pregameheader-file"), "ui-icon-folder-open");
      app.createButton($("#generate-addon-button"), "ui-icon-wrench");
      app.createGenerateAddonPanel();
   };
   
   /**
    * Sets up a tab that allows an admin to update Rocinante's database from The Elder Scrolls
    * Online® language files.
    * 
    * @method setupAdminUpdateDatabaseTab
    */
   app.setupAdminUpdateDatabaseTab = function () {
      app.createButton($("#select-en-client"), "ui-icon-folder-open");
      app.createButton($("#select-fr-client"), "ui-icon-folder-open");
      app.createButton($("#select-en-pregame"), "ui-icon-folder-open");
      app.createButton($("#select-fr-pregame"), "ui-icon-folder-open");
      app.createButton($("#select-en-lang"), "ui-icon-folder-open");
      app.createButton($("#select-fr-lang"), "ui-icon-folder-open");
      app.createButton($("#update-database-button"), "ui-icon-wrench");
      app.createButton($("#maintenance-mode-button"), "ui-icon-gear");
      app.createMaintenanceModeDialog($("#maintenance-mode-dialog"), 550);
      app.bindDialog($("#maintenance-mode-button"), $("#maintenance-mode-dialog"));
      app.createUpdateDatabasePanel();
   };
};

/**
 * Application main constructor. Rocinante is a web app developed to translate The Elder Scrolls
 * Online. It's a popular videogame by Zenimax Online Studios (Bethesda Softworks).
 * http://www.elderscrollsonline.com/
 *
 * @class Rocinante
 * @constructor
 */
function Rocinante() {
   // Turning arguments into an array.
   var args = Array.prototype.slice.call(arguments);
   // The last argument is the callback.
   var callback = args.pop();
   // Modules can be passed as an array or as individual parameters.
   var modules = (args[0] && typeof args[0] === "string") ? args : args[0];
   // For iteration.
   var i, max;
   // A localization object (private).
   var l10n;
   // The translation button set.
   var buttonset = null;

   // This function must be called as a constructor.
   if (!(this instanceof Rocinante)) {
      return new Rocinante(modules, callback);
   }

   /**
    * A utility that manages localization parameters that have been requested to the server. These
    * parameters are set during installation process.
    *
    * @class Localization
    * @constructor
    * @param {object} data An object with the following properties: decimalMark, thousandsMark,
    * dateTimeFormat, dateFormat, timeFormat, okCaptionButton, and cancelCaptionButton.
    */
   function Localization(data) {
      // The localization data.
      var l10n = data;

      // This function must be called as a constructor.
      if (!(this instanceof Localization)) {
         return new Localization(data);
      }

      /**
       * Returns localization data used by Rocinante tables.
       *
       * @return {Object} See rtable documentation.
       */
      this.getTable = function () {
         return l10n;
      };

      /**
       * Returns a caption for 'OK' buttons.
       *
       * @return {String} A localized word/expression for 'OK'.
       */
      this.getOkCaption = function () {
         return l10n.dialog.ok;
      };

      /**
       * Returns a caption for 'Cancel' buttons.
       *
       * @return {String} A localized word/expression for 'Cancel'.
       */
      this.getCancelCaption = function () {
         return l10n.dialog.cancel;
      };

      /**
       * Returns a caption for 'Yes' buttons.
       *
       * @return {String} A localized word/expression for 'Yes'.
       */
      this.getYesLabel = function () {
         return l10n.dialog.yes;
      };
      
      /**
       * Returns a caption for 'No' buttons.
       *
       * @return {String} A localized word/expression for 'No'.
       */
      this.getNoLabel = function () {
         return l10n.dialog.no;
      };
      
      /**
       * Returns a caption for 'Send' buttons.
       *
       * @return {String} A localized word/expression for 'Send'.
       */
      this.getSendCaption = function () {
         return l10n.dialog.send;
      };
      
      /**
       * Returns a caption for 'Save' buttons.
       *
       * @return {String} A localized word/expression for 'Save'.
       */
      this.getSaveCaption = function () {
         return l10n.dialog.save;
      };
      
      /**
       * Returns the translation tabs.
       *
       * @return {Array} An array where each value is a tip.
       */
      this.getTips = function () {
         return l10n.tips;
      };
      
      /**
       * Returns a caption for task tabs.
       *
       * @return {String} A localized word/expression for 'Task No.'.
       */
      this.getTaskCaption = function () {
         return l10n.misc.task;
      };
      
      /**
       * Returns the caption for code dialogs.
       *
       * @return {String} A localized word/expression for 'Code'.
       */
      this.getCodeCaption = function () {
         return l10n.misc.code;
      };
      
      /**
       * Returns a caption for search tabs in Lang.
       *
       * @return {String} A localized word/expression for 'Search'.
       */
      this.getSearchLangCaption = function () {
         return l10n.misc.searchLang;
      };
      
      /**
       * Returns a caption for search tabs in Lua.
       *
       * @return {String} A localized word/expression for 'Search (ui)' where ui is the name of user
       * interface string table.
       */
      this.getSearchLuaCaption = function () {
         return l10n.misc.searchLua;
      };
      
      /**
       * Returns the name of the jQuery UI theme that's used.
       *
       * @return {String} A jQuery UI theme.
       */
      this.getTheme = function () {
         return l10n.misc.theme;
      };
      
      /**
       * Returns a task type from its localized string.
       * @param {String} string A localized string for 'translation', 'revision', 'updating', or
       * 'glossary'.
       * @return {String} A task type: TRANSLATION, REVISION, UPDATING, or GLOSSARY.
       */
      this.getTaskType = function (string) {
         return l10n.taskType[string];
      };
   }

   /**
    * Sets localization data.
    *
    * @param {object} data An object with the following properties: decimalMark, thousandsMark,
    * dateTimeFormat, dateFormat, timeFormat, okCaptionButton, and cancelCaptionButton.
    */
   this.setLocalization = function (data) {
      l10n = new Localization(data);
   };

   /**
    * Gets localization data.
    *
    * @return {Rocinante.Localization} The utility that manages localization parameters.
    */
   this.getLocalization = function () {
      return l10n;
   };

   /**
    * Sets translation button set.
    *
    * @param {string} data HTML buttons that help users to translate.
    */
   this.setTranslationButtonSet = function (data) {
      buttonset = data;
   };

   /**
    * Gets translation button set.
    *
    * @return {string} data HTML buttons that help users to translate.
    */
   this.getTranslationButtonSet = function () {
      return buttonset;
   };

   // Add modules to the core 'this' object.
   // No modules or "*" both mean "use all modules".
   if (!modules || modules[0] === '*') {
      modules = [];
      for (i in Rocinante.modules) {
         if (Rocinante.modules.hasOwnProperty(i)) {
            modules.push(i);
         }
      }
   }

   // Initialize the required modules.
   for (i = 0, max = modules.length; i < max; i += 1) {
      Rocinante.modules[modules[i]](this);
   }

   // Call the callback.
   callback(this);
}

// Any prototype properties as needed.
Rocinante.prototype = {
   name: "Rocinante",
   version: "1.0.1",
   getName: function () {
      return this.name;
   }
};

// Entry function.
$(document).ready(function () {   
   new Rocinante('*', function (app) {
      app.start();
      app.localize();
      app.setupFrontPage();
      app.setupUpdateCurrentUserDialog();
      app.setupTranslationHelpers();
      app.setupTaskTab();
      app.setupNewTaskDialog();
      app.setupReassignTaskDialog();
      app.setupReviseTaskDialog();
      app.setupDeleteTaskDialog();
      app.setupMailTab();
      app.setupNewMessageDialog();
      app.setupNewReplyDialog();
      app.setupNewMessageFromDraftDialog();
      app.setupAdminStatsTab();
      app.setupAdminUserManagementTab();
      app.setupAdminTaskManagementTab();
      app.setupAdminAddonTab();
      app.setupAdminUpdateDatabaseTab();
      app.refresh();
      $("body").show();
   });
});
