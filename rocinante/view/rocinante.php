<?php

namespace rocinante\view;

require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/command/SessionRegistry.php';

$session = \rocinante\command\SessionRegistry::instance();
$helper = \rocinante\view\ViewHelper::instance();
$helper->loadThemeData($session->getTheme());
$l10n = $helper->getL10n();
$fp = $l10n->frontpage;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo $l10n->title ?></title>
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.4/themes/<?php echo $helper->getTheme() ?>/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/rocinante.css" >
<link rel="stylesheet" type="text/css" href="css/rtable.css" >
<script src="https://code.jquery.com/jquery-2.2.2.min.js"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/jquery.rtable.js?<?php echo time() ?>"></script>
<script src="js/jquery.raccordion.js?<?php echo time() ?>"></script>
<script src="js/jquery.langtable.js?<?php echo time() ?>"></script>
<script src="js/rocinante.js?<?php echo time() ?>"></script>
</head>
<body class="hide" style="background-color: <?php echo $helper->getBackground() ?>">
   <header class="ui-widget-header ui-corner-all header">
      <div class="tablecell" id="left-text">
         <img src="images/<?php echo $helper->getLogo() ?>" alt="Rocinante">
      </div>
      <div class="ui-widget tablecell right-text" id="translation-status">
         <!-- Traducidos 50.000 de 100.000 textos (50,00%) -->
      </div>
      <div class="tablecell right-text">
         <button class="ui-state-default" id="download-button"><?php echo $fp->{"download-button"} ?></button>
         <button class="ui-state-default" id="user-button"></button>
         <button class="ui-state-default" id="logout-button"><?php echo $fp->{"logout-button"} ?></button>
      </div>
   </header>

   <div class="ui-state-error ui-corner-all warning-padding header-spacing" id="maintenance-mode-status">
      <p style="font-size: large; text-align: center"><strong><?php echo $l10n->{"maintenance-warning"} ?></strong></p>
   </div>
   
   <div class="ui-state-highlight ui-corner-all warning-padding header-spacing" id="important-info">
      <p style="font-size: large; text-align: center"><strong></strong></p>
   </div>
   
   <!-- Tips for translating properly -->
   <div class="ui-widget-content ui-corner-all header-spacing" id="motd"></div>

   <!-- Info and search bar -->
   <div class="ui-widget-header ui-corner-all" id="search">
      <div class="tablecell left-text">
         <button class="ui-state-default" id="task-button"></button> <!-- Tiene 3 tareas pendientes -->
         <button class="ui-state-default" id="mail-button"></button> <!-- No tiene mensajes nuevos -->
      </div>
      <form action="#">
         <p><input class="ui-corner-all" type="text" name="search-text" id="search-text" placeholder="<?php echo $fp->{"search-caption"} ?>" /></p> <!-- Búsqueda -->
      </form>
   </div>

   <div class="ui-state-highlight ui-corner-all header-spacing warning-padding" style="font-size: 1.2em;" id="current-user-info">
      <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
   </div>
   
   <!-- Tabs -->
   <div class="header-spacing" id="main-tabs">
      <ul id="tab-list">
         <li><a href="#status"><?php echo $fp->{"tabs"}->{"master-table"}['text'] ?></a></li> <!-- Estado -->
         <li><a href="#tasks"><?php echo $fp->{"tabs"}->{"tasks"}['text'] ?></a></li> <!-- Tareas -->
         <li><a href="#mail"><?php echo $fp->{"tabs"}->{"mail"}['text'] ?></a></li> <!-- Correo -->
         <?php if ($session->getType() === "ADMIN") { echo '<li><a href="#admin">' . $fp->{"tabs"}->{"admin"}['text'] . '</a></li>'; } ?>
      </ul>

      <div id="status">
         <!-- Status tab -->
         <table id="main-table">
            <!-- Master table is here -->
         </table>
      </div>

      <!-- Task tab -->
      <div id="tasks">
         <button class="ui-state-default" id="new-task-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"new-task-button"} ?></button>
         <button class="ui-state-default" id="open-task-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"open-task-button"} ?></button>
         <button class="ui-state-default" id="reassign-task-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"reassign-task-button"} ?></button>
         <button class="ui-state-default" id="revise-task-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"revise-task-button"} ?></button>
         <button class="ui-state-default" id="delete-task-button"><?php echo $fp->{"tabs"}->{"tasks"}->{"delete-task-button"} ?></button>

         <div class="ui-state-highlight ui-corner-all header-spacing warning-padding" id="user-task-info">
            <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
         </div>

         <div class="ui-widget-header ui-corner-all header-spacing header-padding">
            <strong><?php echo $fp->{"tabs"}->{"tasks"}->{"user-task-table-caption"} ?></strong>
         </div>
         <table id="user-task-table">
            <!-- User task table is here -->
         </table>
         <!-- Task table with those tasks assigned by the user is here -->
         <?php if ($session->getType() !== "TRANSLATOR") { include "rocinante/view/assigner_task_table.php"; } ?>
      </div>

      <!-- Mail tab -->
      <div id="mail">
         <button class="ui-state-default" id="new-message-button"><?php echo $fp->{"tabs"}->{"mail"}->{"new-message-button"} ?></button>
         <button class="ui-state-default" id="new-reply-button"><?php echo $fp->{"tabs"}->{"mail"}->{"new-reply-button"} ?></button>
         <button class="ui-state-default" id="new-reply-all-button"><?php echo $fp->{"tabs"}->{"mail"}->{"new-reply-all-button"} ?></button>
         <button class="ui-state-default" id="open-draft-button"><?php echo $fp->{"tabs"}->{"mail"}->{"open-draft-button"} ?></button>
         <button class="ui-state-default" id="delete-message-button"><?php echo $fp->{"tabs"}->{"mail"}->{"delete-message-button"} ?></button>
         
         <div class="ui-state-highlight ui-corner-all header-spacing warning-padding" id="user-mail-info">
            <p><span class="ui-icon ui-icon-info warning-icon-pos"></span><strong></strong></p>
         </div>
         
         <div class="header-spacing header-padding" id="mail-tabs">
            <ul>
               <li><a href="#mail-inbox"><?php echo $fp->{"tabs"}->{"mail"}->{"tabs"}->{"inbox"} ?></a></li>
               <li><a href="#mail-outbox"><?php echo $fp->{"tabs"}->{"mail"}->{"tabs"}->{"outbox"} ?></a></li>
               <li><a href="#mail-drafts"><?php echo $fp->{"tabs"}->{"mail"}->{"tabs"}->{"drafts"} ?></a></li>
            </ul>
            
            <div id="mail-inbox"></div>
            <div id="mail-outbox"></div>
            <div id="mail-drafts"></div>
         </div>
      </div>
      
      <!-- Admin contents -->
      <?php if ($session->getType() === "ADMIN") { include "rocinante/view/admin.php"; } ?>
   </div>

   <!-- Dialog to update the user account -->
   <?php include "rocinante/view/update_current_user_dialog.php"; ?>   
   <!-- Dialog to update an ESO table description -->
   <?php include "rocinante/view/update_esotable_dialog.php"; ?>
   <!-- Dialog to show help about an ESO code -->
   <?php include "rocinante/view/eso_codes.php"; ?>
   <!-- Dialog to add a new term to the glossary -->
   <?php include "rocinante/view/new_term_dialog.php"; ?>
   <!-- Dialog to update a glossary term -->
   <?php include "rocinante/view/update_term_dialog.php"; ?>
   <!-- Dialog to add a new task -->
   <?php include "rocinante/view/new_task_dialog.php"; ?>
   <!-- Dialog to select an ESO table -->
   <?php include "rocinante/view/select_esotable_dialog.php"; ?>
   <!-- Dialogs to update a task -->
   <?php include "rocinante/view/reassign_task_dialog.php"; ?>
   <?php include "rocinante/view/revise_task_dialog.php"; ?>
   <!-- Dialog to delete a task -->
   <?php include "rocinante/view/delete_task_dialog.php"; ?>
   <!-- Dialog to write a message -->
   <?php include "rocinante/view/new_message_dialog.php"; ?>
   <!-- Dialog to select an addreessee -->
   <?php include "rocinante/view/select_user_dialog_1.php"; ?>
   <!-- Dialog to reply to a message -->
   <?php include "rocinante/view/new_reply_dialog.php"; ?>
   <!-- Dialog to select an addreessee -->
   <?php include "rocinante/view/select_user_dialog_2.php"; ?>
   <!-- Dialog to write a message from a draft -->
   <?php include "rocinante/view/new_message_from_draft_dialog.php"; ?>
   <!-- Dialog to select an addreessee -->
   <?php include "rocinante/view/select_user_dialog_3.php"; ?>
   
   <!-- Dialog for creating a new user -->
   <?php if ($session->getType() === "ADMIN") { include "rocinante/view/new_user_dialog.php"; } ?>
   <!-- Dialog for modifying a user account -->
   <?php if ($session->getType() === "ADMIN") { include "rocinante/view/update_user_dialog.php"; } ?>
   <!-- Dialog for deleting a user -->
   <?php if ($session->getType() === "ADMIN") { include "rocinante/view/delete_user_dialog.php"; } ?>
   <!-- Dialog for re-assigning a task -->
   <?php if ($session->getType() === "ADMIN") { include "rocinante/view/reassign_task_admin_dialog.php"; } ?>
   <!-- Dialog for revising a task -->
   <?php if ($session->getType() === "ADMIN") { include "rocinante/view/revise_task_admin_dialog.php"; } ?>
   <!-- Dialog for deleting a task -->
   <?php if ($session->getType() === "ADMIN") { include "rocinante/view/delete_task_admin_dialog.php"; } ?>
   <!-- Dialog for setting maintenance mode -->
   <?php if ($session->getType() === "ADMIN") { include "rocinante/view/maintenance_mode_dialog.php"; } ?>
</body>
</html>
