<?php

namespace rocinante\command;

require_once 'rocinante/controller/AppController.php';

/**
 * FrontController defines a central point of entry for every request. It processes the request to 
 * select an operation (a command) that is executed.
 */
class FrontController
{

   /**
    * The application controller.
    * @var AppController 
    */
   private $appController;

   /**
    * FrontController can't be instanced directly.
    */
   private function __contruct()
   {
      
   }

   /**
    * Creates the unique Controller instance, initializes application helper, and handle a request.
    */
   public static function run()
   {
      $instance = new FrontController();
      $instance->init();
   }

   /**
    * Initializes the application helper.
    */
   protected function init()
   {
      $this->appController = new \rocinante\controller\AppController();
      $this->appController->handleRequest();
   }

}
