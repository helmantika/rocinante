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
$(document).ready(function () {
   $("body").show();
   $(":submit").hide();
   $(":text").focus();
   $("#wrong-data").hide();
   $("input[name=password]").keypress(function (event) {
      if (event.which === 13) { // the enter key code
         $.ajax({
            method: "POST",
            url: "controller.php",
            dataType: "text",
            data: {cmd: "login/Login", username: $("input[name=username]").val(), password: $("input[name=password]").val()},
            success: function (data) {
               if (data === 'null') {
                  $(":password").val("").focus();
                  $("#wrong-data").show();
               } else {
                  location.reload();
               }
            },
            error: function (e, xhr) {
               console.log(xhr);
            }
         });
         event.preventDefault();
      }
   });
});
