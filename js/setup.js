/*
 *  Rocinante. The Elder Scrolls Online Translation Web App.
 *  Copyright (c) 2017 Jorge Rodríguez Santos
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
 * @type {RocinanteSetup.args|Array}
 */
RocinanteSetup.modules = {};

/**
 * A module that manages the user interface and it is based on jQuery UI capabilities.
 *
 * @param {RocinanteSetup} app A RocinanteSetup object.
 */
RocinanteSetup.modules.ui = function (app) {
   
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
    * @param {Number} width The combo box width given in pixels.
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
    * Creates a panel to update the database.
    * 
    * @method createPopulateDatabasePanel
    */
   app.createPopulateDatabasePanel = function () {
      var selectedEnClientFile = {};
      var selectedFrClientFile = {};
      var selectedEnPregameFile = {};
      var selectedFrPregameFile = {};
      var selectedEnLangFile = {};
      var selectedFrLangFile = {};
      
      $("#browse-update-files-row-1").hide();
      $("#browse-update-files-row-2").hide();
      $("#browse-update-files-row-3").hide();
      $("#uploading-files").hide();
      $("#failed-load").hide();
      
      app.createButton($("#select-en-client"), "ui-icon-folder-open");
      app.createButton($("#select-fr-client"), "ui-icon-folder-open");
      app.createButton($("#select-en-pregame"), "ui-icon-folder-open");
      app.createButton($("#select-fr-pregame"), "ui-icon-folder-open");
      app.createButton($("#select-en-lang"), "ui-icon-folder-open");
      app.createButton($("#select-fr-lang"), "ui-icon-folder-open");
      
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
      
      /**
       * Allows to handle (set/get) values of the panel.
       * @property PopulateDatabasePanel
       * @type {Object}
       */
      app.PopulateDatabasePanel = {
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
   
   /**
    * Creates a panel to load a CSV file.
    * 
    * @method createLoadCsvFilePanel
    */
   app.createLoadCsvFilePanel = function () {
      var selectedCsvFile = {};
      
      $("#browse-csv-file-row").hide();
      
      app.createButton($("#select-csv-file"), "ui-icon-folder-open");
      
      $("#browse-csv-file").on("change", function (event) {
         selectedCsvFile = event.target.files;
         $("#csvfile").val(selectedCsvFile[0].name);
      });

      $("#select-csv-file").click(function (event) {
         event.preventDefault();
         $("#browse-csv-file").trigger('click');
      });
      
      /**
       * Allows to handle (set/get) values of the panel.
       * @property LoadCsvFilePanel
       * @type {Object}
       */
      app.LoadCsvFilePanel = {
         /**
          * Returns file.csv
          * @method getSelectedCsvFile
          * @return {FileList} An object that contains the selected file.
          */
         getSelectedCsvFile: function () {
            return selectedCsvFile[0];
         }
      };
   };
   
   /**
    * Creates a panel to create the first user.
    * 
    * @method createNewAdminPanel
    */
   app.createNewAdminPanel = function () {
      var selectedUserGender = "MALE";
      
      app.createComboBox($("#select-user-gender"), 180, 0, function (event, ui) {
         selectedUserGender = ui.item.value;
      });
      
      var userGenderSelectmenu = $("#select-user-gender");
      userGenderSelectmenu.val(selectedUserGender);
      userGenderSelectmenu.selectmenu("refresh");
      
      /**
       * Allows to handle (set/get) values of the dialog.
       * @property modifyUserDialog
       * @type {Object}
       */
      app.newAdminPanel = {
         /**
          * Returns selected user gender.
          * @method getSelectedGender
          * @return {String} MALE, or FEMALE.
          */
         getSelectedGender: function () {
            return selectedUserGender;
         }
      };
   };
};

/**
 * A module that manages every type of event.
 *
 * @param {RocinanteSetup} app A RocinanteSetup object.
 */
RocinanteSetup.modules.events = function (app) {
   
   /**
    * Starts localizated setup.
    * @param {Event} event Event data.
    */
   $("#start-button").click(function (event) {
      app.startSetup();
      event.preventDefault();
   });
   
   /**
    * Sets events for UI widgets.
    * 
    * @method init
    */
   app.init = function () {
      $(".goto-files-setup-button").click(function (event) {
         app.createDatabase();
         event.preventDefault();
      });
      
      $(".goto-target-language-setup-button").click(function (event) {
         $("#files-setup").hide();
         $("#target-language-setup").show();
         event.preventDefault();         
      });
      
      $("#prev-button").click(function (event) {
         $("#target-language-setup").hide();
         $("#files-setup").show();
         event.preventDefault();
      });
      
      $(".goto-admin-setup-button").click(function (event) {
         app.populateDatabase();
         event.stopPropagation();
         event.preventDefault();
      });
      
      $(".goto-end-setup-button").click(function (event) {
         app.insertUser();
         event.preventDefault();
      });
      
      $("#finish-button").click(function (event) {
         event.preventDefault();
         window.location.href = 'index.php';
      });
   
      $("#test-connection-button").click(function (event) {
         app.testConnection();
         event.preventDefault();
      });
   };
};

/**
 * A module that makes asynchronous requests to the server in order to read, write, and modify data.
 *
 * @param {RocinanteSetup} app A RocinanteSetup object.
 */
RocinanteSetup.modules.ajax = function (app) {

   /**
    * Requests available languages.
    * 
    * @method requestLanguages
    */
   app.requestLanguages = function () {
      var selectedLanguage;

      $.ajax({
         async: false,
         method: "POST",
         url: "controller.php",
         data: {cmd: "setup/ListLanguages"},
         dataType: "json",
         success: function (data) {
            var languageSelectmenu = $("#select-webapp-language");
            for (var key in data) {
               languageSelectmenu.append($("<option>").attr("value", key).text(data[key].name));
            }
            selectedLanguage = languageSelectmenu.val();
            $("#start-button").text(data[selectedLanguage].button);
            app.createButton($("#start-button"));
            app.createComboBox(languageSelectmenu, 200, 0, function (event, ui) {
               selectedLanguage = ui.item.value;
               $("#start-button").button("destroy").text(data[selectedLanguage].button);
               app.createButton($("#start-button"));
            });
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });

      /**
       * Returns selected language.
       * @method getSelectedLanguage
       * @return {String} A language code.
       */
      app.getSelectedLanguage = function () {
         return selectedLanguage;
      };
   };

   /**
    * Requests setup_process.php contents according to selected language.
    * 
    * @method startSetup
    */
   app.startSetup = function () {
      $.ajax({
         method: "POST",
         url: "controller.php",
         data: {cmd: "setup/StartSetup", language: app.getSelectedLanguage()},
         dataType: "html",
         success: function (data) {
            app.start(data);
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };

   /**
    * Requests a database connection test.
    * 
    * @method testConnection
    */
   app.testConnection = function() {
      var form = $("#database-setup");
      var td = form.find("td");
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "setup/TestConnection",
                host: {field: td.eq(0).text(),
                       value: form.find("input[name=host]").val()},
                database: {field: td.eq(2).text(),
                            value: form.find("input[name=database]").val()},
                username: {field: td.eq(4).text(),
                           value: form.find("input[name=username]").val()},
                password: {field: td.eq(6).text(),
                           value: form.find("input[name=password]").val()}
         },
         success: function (data) {
            if (data.result === 'null') {
               form.find("#right-connection").hide();
               form.find("#failed-connection").find("#mysql-error").html(data.html);
               form.find("#failed-connection").show();
            }
            else {
               form.find("#failed-connection").hide();
               form.find("#right-connection").find("#mysql-ok").html(data.html);
               form.find("#right-connection").show();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a database structure creation.
    * 
    * @method createDatabase
    */
   app.createDatabase = function() {
      var form = $("#database-setup");
      var td = form.find("td");
      
      $("#creating-database").show();
      $("#test-connection-button").prop("disabled", true);
      $(".goto-files-setup-button").prop("disabled", true);
      
      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "setup/CreateDatabase",
                host: {field: td.eq(0).text(),
                       value: form.find("input[name=host]").val()},
                database: {field: td.eq(2).text(),
                            value: form.find("input[name=database]").val()},
                username: {field: td.eq(4).text(),
                           value: form.find("input[name=username]").val()},
                password: {field: td.eq(6).text(),
                           value: form.find("input[name=password]").val()}
         },
         success: function (data) {
            if (data.result === 'null') {
               $("#creating-database").hide();
               $("#test-connection-button").prop("disabled", false);
               $(".goto-files-setup-button").prop("disabled", false);
               
               form.find("#right-connection").hide();
               form.find("#failed-connection").find("#mysql-error").html(data.html);
               form.find("#failed-connection").show();
            }
            else {
               form.hide();
               $("#files-setup").show();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
   
   /**
    * Requests a database population from The Elders Scrolls Online language file set.
    *
    * @method populateDatabase
    */
   app.populateDatabase = function () {
      var form = $("#target-language-setup");
      
      // Create a formdata object and add the command name, the files, and the version number.
      var data = new FormData();
      data.append('cmd', 'setup/PopulateDatabase');
      data.append('targetlang', form.find("input[name=targetlang]").val());
      data.append(0, app.PopulateDatabasePanel.getSelectedFrClientFile());
      data.append(1, app.PopulateDatabasePanel.getSelectedFrPregameFile());
      data.append(2, app.PopulateDatabasePanel.getSelectedFrLangFile());
      data.append(3, app.PopulateDatabasePanel.getSelectedEnClientFile());
      data.append(4, app.PopulateDatabasePanel.getSelectedEnPregameFile());
      data.append(5, app.PopulateDatabasePanel.getSelectedEnLangFile());
      data.append(6, app.LoadCsvFilePanel.getSelectedCsvFile());
      
      $("#uploading-files").show();
      $("#prev-button").prop("disabled", true);
      $(".goto-admin-setup-button").prop("disabled", true);

      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         cache: false,
         processData: false, // Don't process the file.
         contentType: false, // Don't set content type.
         data: data,
         timeout: 10800000,
         success: function (data) {
            if (data.result === 'null') {
               $("#uploading-files").hide();
               $("#prev-button").prop("disabled", false);
               $(".goto-admin-setup-button").prop("disabled", false);
               
               form.find("#failed-load").find("#load-error").html(data.html);
               form.find("#failed-load").show();
            }
            else {
               form.hide();
               $("#admin-setup").show();
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
    */
   app.insertUser = function () {
      var form = $("#admin-setup");
      var td = form.find("td");

      $.ajax({
         method: "POST",
         url: "controller.php",
         dataType: "json",
         data: {cmd: "user/InsertUser",
                username: {field: td.eq(0).text(),
                           value: form.find("input[name=username]").val()},
                password: {field: td.eq(2).text(),
                           value: form.find("input[name=password]").val()},
                passwordv: {field: td.eq(4).text(),
                            value: form.find("input[name=passwordv]").val()},
                type: {field: 'type', value: 'ADMIN'},
                name: {field: td.eq(6).text(),
                       value: form.find("input[name=name]").val()},
                gender: {field: td.eq(8).text(),
                         value: app.newAdminPanel.getSelectedGender()},
                email: {field: td.eq(10).text(),
                        value: form.find("input[name=email]").val()},
                advisor: {field: 'advisor', value: 0}
         },
         success: function (data) {
            if (data.result === 'null') {
               form.find("#bad-user").find("#user-error").html(data.html);
               form.find("#bad-user").show();
            } else {
               form.hide();
               $("#end-setup").show();
            }
         },
         error: function (e, xhr) {
            console.log(xhr);
         }
      });
   };
};

/**
 * A module that initializes the application.
 *
 * @param {RocinanteSetup} app A RocinanteSetup object.
 */
RocinanteSetup.modules.init = function (app) {
     
   /**
    * Starts setup after setting its language.
    * 
    * @method start
    * @param {string} data The setup_process.php file given as a string.
    */
   app.start = function(data) {
      $("#content").empty().append(data);
      
      $("#files-setup").hide();
      $("#target-language-setup").hide();
      $("#admin-setup").hide();
      $("#end-setup").hide();
      
      $("#failed-connection").hide();
      $("#right-connection").hide();
      $("#creating-database").hide();
      $("#bad-user").hide();
      
      app.createButton($("#test-connection-button"));
      app.createButton($("#prev-button"));
      app.createButton($(".goto-database-setup-button"));
      app.createButton($(".goto-files-setup-button"));
      app.createButton($(".goto-target-language-setup-button"));
      app.createButton($(".goto-admin-setup-button"));
      app.createButton($(".goto-end-setup-button"));
      app.createButton($("#finish-button"));
      
      app.createPopulateDatabasePanel();
      app.createLoadCsvFilePanel();
      app.createNewAdminPanel();
      
      app.init();
   };
};

/**
 * Application main constructor. Rocinante is a web app developed to translate The Elder Scrolls
 * Online, a popular videogame by Zenimax Online Studios (Bethesda Softworks).
 * http://www.elderscrollsonline.com/
 *
 * @class RocinanteSetup
 * @constructor
 */
function RocinanteSetup() {
   // Turning arguments into an array.
   var args = Array.prototype.slice.call(arguments);
   // The last argument is the callback.
   var callback = args.pop();
   // Modules can be passed as an array or as individual parameters.
   var modules = (args[0] && typeof args[0] === "string") ? args : args[0];
   // For iteration.
   var i, max;

   // This function must be called as a constructor.
   if (!(this instanceof RocinanteSetup)) {
      return new RocinanteSetup(modules, callback);
   }

   // Add modules to the core 'this' object.
   // No modules or "*" both mean "use all modules".
   if (!modules || modules[0] === '*') {
      modules = [];
      for (i in RocinanteSetup.modules) {
         if (RocinanteSetup.modules.hasOwnProperty(i)) {
            modules.push(i);
         }
      }
   }

   // Initialize the required modules.
   for (i = 0, max = modules.length; i < max; i += 1) {
      RocinanteSetup.modules[modules[i]](this);
   }

   // Call the callback.
   callback(this);
}

// Any prototype properties as needed.
RocinanteSetup.prototype = {
   name: "RocinanteSetup",
   version: "1.0.0",
   getName: function () {
      return this.name;
   }
};

// Entry function.
$(document).ready(function () {
   new RocinanteSetup('*', function (app) {
      app.requestLanguages();
      $("body").show();
   });
});
