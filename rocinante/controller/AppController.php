<?php

namespace rocinante\controller;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/controller/RequestRegistry.php';
require_once 'rocinante/command/DefaultCommand.php';

/**
 * AppController decides how to interpret an HTTP request so that it can invoke the right code to
 * fulfill that request.
 */
class AppController
{

   /**
    * The base class for commands.
    * @var Command
    */
   private $baseCommand = null;

   /**
    * The default command. If something goes wrong, it will be returned.
    * @var \rocinante\command\DefaultCommand
    */
   private $defaultCommand = null;

   /**
    * Sets the base and default commands.
    */
   public function __construct()
   {
      $this->baseCommand = new \ReflectionClass("\\rocinante\\controller\\Command");
      $this->defaultCommand = new \rocinante\command\DefaultCommand();
   }

   /**
    * Gets a command object.
    * @param \rocinante\command\Request $request A request.
    * @return Command A command or null whether there's no a forward command.
    * @throws \Exception Command not found and circular forwarding between commands.
    */
   public function getCommand(\rocinante\controller\Request $request)
   {
      $command = null;
      $cmd = $request->getProperty('cmd');

      if (!\is_null($cmd))
      {
         $command = $this->resolveCommand($cmd);
         if (\is_null($command))
         {
            throw new \Exception("'$cmd' cannot be resolved");
         }
      }

      return $command;
   }

   /**
    * Looks for a command. If it's found, it maps to a real class file in the command directory, and
    * the class file contains the right kind of class then creates and returns an instance of the 
    * relevant class.
    * @param string $command A command name.
    * @return Command An instance of the suitable command, or null if something went wrong.
    */
   public function resolveCommand($command)
   {
      $instance = null;
      $cmd = \str_replace(".", "", $command);
      $filepath = "rocinante/command/$cmd.php";
      $classcmd = \str_replace("/", "\\", $cmd);
      $classname = "\\rocinante\\command\\$classcmd";

      if (\file_exists($filepath))
      {
         require_once($filepath);
         if (\class_exists($classname))
         {
            $commandClass = new \ReflectionClass($classname);
            if ($commandClass->isSubclassOf($this->baseCommand))
            {
               $instance = $commandClass->newInstance();
            }
         }
      }

      return $instance;
   }

   /**
    * Reads a request, acquires one or more commands based on that request, executes them, and 
    * invokes a view.
    */
   public function handleRequest()
   {
      $request = \rocinante\controller\RequestRegistry::getRequest();
      $command = $this->getCommand($request);
      if ($command !== null)
      {
         $command->execute($request);
      }
   }

}
