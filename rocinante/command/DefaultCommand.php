<?php

namespace rocinante\command;

require_once 'rocinante/controller/Command.php';

/**
 * DefaultCommand is executed when AppController doesn't know which command has to be instanced.
 */
class DefaultCommand extends \rocinante\controller\Command
{

   /**
    * Actually executes the command implemented by subclasses.
    */
   public function doExecute()
   {

   }

}
