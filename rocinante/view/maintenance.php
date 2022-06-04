<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';

$helper = \rocinante\view\ViewHelper::instance();
$helper->loadThemeData();
$l10n = $helper->getL10n();
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="UTF-8">
      <title><?php echo $l10n->title ?></title>
      <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.4/themes/<?php echo $helper->getTheme() ?>/jquery-ui.min.css">
      <link rel="stylesheet" type="text/css" href="css/login.css">
   </head>
   <body style="background-color: <?php echo $helper->getBackground() ?>">
      <div id="main">
         <header class="ui-widget-header ui-corner-all">
            <img src="images/<?php echo $helper->getLogo() ?>" alt="Rocinante">
         </header>
         <div class="ui-state-error ui-corner-all header-spacing" id="wrong-data">
            <p><?php echo $l10n->loginpage->maintenance ?></p>
         </div>
         <div class="ui-widget-header ui-corner-all" id="info"><?php echo $l10n->loginpage->about . "<br />" . $l10n->loginpage->join ?></div>
      </div>
      <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
      <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
   </body>
</html>
