<?php
\ini_set('display_errors', 1);
\ini_set('display_startup_errors', 1);
error_reporting(\E_ALL);
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="UTF-8">
      <title>Rocinante</title>
      <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.4/themes/hot-sneaks/jquery-ui.min.css">
      <link rel="stylesheet" type="text/css" href="css/setup.css">
   </head>
   <body class="hide" style="background-color: #eeeeee">
      <div class="outer">
         <div class="middle">
            <div class="inner">
               <header class="ui-widget-header ui-corner-all">
                  <img src="images/rocinante-white-logo.png" alt="Rocinante">
               </header>
               <div id="content">
                  <div class="ui-widget ui-corner-all content" id="language-selection">
                     <form action="#">
                        <select name="language" id="select-webapp-language"></select>
                     </form>
                  </div>
                  <div class="ui-widget-header ui-corner-all" id="info">
                     <button class="ui-state-default" id="start-button"></button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
      <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
      <script src="js/setup.js?<?php echo time() ?>"></script>
   </body>
</html>
