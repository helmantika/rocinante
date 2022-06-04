<?php

namespace rocinante\controller;

require_once 'rocinante/controller/Request.php';

/**
 * RequestRegistry defines an interface to provide systemwide access to the last request.
 */
class RequestRegistry
{

   /**
    * The request values.
    * @var array
    */
   private $values = array();

   /**
    * The one and only instance of this class.
    * @var RequestRegistry
    */
   private static $instance = null;

   /**
    * RequestRegistry can't be instanced directly.
    */
   private function __construct()
   {

   }

   /**
    * Returnst the one and only instance of this class.
    * @return RequestRegistry The RequestRegistry instance.
    */
   public static function instance()
   {
      if (is_null(self::$instance))
      {
         self::$instance = new self();
      }
      return self::$instance;
   }

   /**
    * Returns the value of the specified key.
    * @param string $key A key given as an string.
    */
   protected function get($key)
   {
      $value = null;
      if (isset($this->values[$key]))
      {
         $value = $this->values[$key];
      }
      return $value;
   }

   /**
    * Sets a value for the specified key.
    * @param string $key A key given as an string.
    * @param mixed $value A value of any type.
    */
   protected function set($key, $value)
   {
      $this->values[$key] = $value;
   }

   /**
    * Returns a new request. If a request is not set then a new one is created.
    * @return Request A request.
    */
   public static function getRequest()
   {
      $instance = self::instance();
      if (is_null($instance->get('request')))
      {
         $instance->set('request', new Request());
      }
      return $instance->get('request');
   }
   
}
