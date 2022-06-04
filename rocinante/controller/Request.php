<?php

namespace rocinante\controller;

/**
 * Request centralizes PHP request managing. A Request object is passed to AppController, and to a 
 * command later.
 */
class Request
{

   /**
    * The request variables.
    * @var array 
    */
   private $properties;

   /**
    * The conduit through which controller classes pass messages to the user.
    * @var array 
    */
   private $feedback = array();
   
   /**
    * Processes a request.
    */
   public function __construct()
   {
      $this->init();
   }

   /**
    * Retrieves request data from a HTTP request or from command line.
    */
   protected function init()
   {
      // If the request method is set then assign request variables.
      $requestMethod = $_SERVER['REQUEST_METHOD'];
      if ($requestMethod === 'GET' || $requestMethod === 'POST')
      {
         // Data must be sanitized by commands.
         $this->properties = $_REQUEST;
      }
      // Request variables were set from command line.
      else
      {
         foreach ($_SERVER['argv'] as $arg)
         {
            if (strpos($arg, '='))
            {
               list( $key, $value ) = \explode('=', $arg);
               $this->setProperty($key, $value);
            }
         }
      }
   }

   /**
    * Gets a property value.
    * @param string $key A property name.
    * @return mixed The property value or null if property is not set.
    */
   public function getProperty($key)
   {
      $value = null;
      if (isset($this->properties[$key]))
      {
         $value = $this->properties[$key];
      }
      return $value;
   }

   /**
    * Sets a property.
    * @param string $key A property name.
    * @param mixed $value A property value.
    */
   public function setProperty($key, $value)
   {
      $this->properties[$key] = $value;
   }

   /**
    * Adds a message for the user.
    * @param string $msg A message.
    */
   public function addFeedback($msg)
   {
      \array_push($this->feedback, $msg);
   }

   /**
    * Gets feedback messages.
    * @return array An array of messages. 
    */
   public function getFeedback()
   {
      return $this->feedback;
   }

   /**
    * Gets a string that contains all the feedback messages.
    * @param string $separator A string to mark the end of every message.
    * @return string A string that contains all the feedback messages.
    */
   public function getFeedbackString($separator = '\n')
   {
      return \implode($separator, $this->feedback);
   }
   
   /**
    * Sets a command the request will perform.
    * @param string $cmd A command name.
    */
   public function setCommand($cmd)
   {
      $this->setProperty('cmd', $cmd);
   }

}
