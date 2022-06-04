<?php

namespace rocinante\controller;

require_once 'rocinante/controller/Request.php';

/**
 * Command defines an interface to declare commands used by the application. Commands encapsulate
 * application logic.
 */
abstract class Command
{
   
   /**
    * The request whose data will be used to perform this command.
    * @var \rocinante\controller\Request 
    */
   protected $request = null;

   /**
    * A Command child can't override this constructor.
    */
   final public function __construct()
   {
      
   }

   /**
    * Executes this command.
    * @param Request $request A request.
    */
   public function execute(\rocinante\controller\Request $request)
   {
      $this->request = $request;
      $this->doExecute();
   }
   
   /**
    * Actually executes the command implemented by subclasses.
    */
   abstract public function doExecute();
}
