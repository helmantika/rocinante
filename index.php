<?php
\ini_set('display_errors', 1);
\ini_set('display_startup_errors', 1);
error_reporting(\E_ALL);

set_include_path(get_include_path() . PATH_SEPARATOR . "./rocinante/command");
set_include_path(get_include_path() . PATH_SEPARATOR . "./rocinante/controller");
set_include_path(get_include_path() . PATH_SEPARATOR . "./rocinante/domain");
set_include_path(get_include_path() . PATH_SEPARATOR . "./rocinante/mapper");
set_include_path(get_include_path() . PATH_SEPARATOR . "./rocinante/persistence");
set_include_path(get_include_path() . PATH_SEPARATOR . "./rocinante/validator");
set_include_path(get_include_path() . PATH_SEPARATOR . "./rocinante/view");

require_once 'rocinante/command/FrontController.php';
require_once 'rocinante/controller/RequestRegistry.php';

$request = \rocinante\controller\RequestRegistry::getRequest();
$request->setCommand('login/CheckSession');
\rocinante\command\FrontController::run();
