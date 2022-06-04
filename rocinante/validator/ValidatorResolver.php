<?php
namespace rocinante\validator;

require_once 'rocinante/validator/Validator.php';

/**
 * ValidatorResolver decides how to interpret a validation requirement so that it can invoke the 
 * right code to fulfill that validation.
 */
class ValidatorResolver
{

   /**
    * The base class for validators.
    * @var Command
    */
   private static $baseValidator = null;

   /**
    * The validators that had already been instanced.
    * @var Array 
    */
   private $validators = array();

   /**
    * Sets the base validator.
    */
   public function __construct()
   {
      if (is_null(self::$baseValidator))
      {
         self::$baseValidator = new \ReflectionClass("rocinante\\validator\\Validator");
      }
   }

   /**
    * Looks for a validator class name. If it's found, it maps to a real class file in the validator
    * directory. Also, if the class file contains the right kind of class then returns an instance
    * of the relevant class.
    * @param String $name Name of a validator class.
    * @return Validator An instance of the suitable validator, or null if something went wrong.
    */
   public function getValidator($name)
   {
      $instance = null;

      // Extract validator name. It can have a param.
      $realname = \preg_filter('/([A-Za-z]+)(\(([0-9]+)\))?/', '$1', $name);

      // Check if the validator had already been instanced.
      if (isset($this->validators[$name]))
      {
         $instance = $this->validators[$name];
      }
      // Create a new object.
      else
      {
         // Sanitize command file name.
         $validator = \str_replace(array('.', '/'), "", $realname);
         $filepath = "rocinante/validator/{$validator}.php";
         $classname = "rocinante\\validator\\{$validator}";
         if (\file_exists($filepath))
         {
            require_once( $filepath );
            if (class_exists($classname))
            {
               $validatorClass = new \ReflectionClass($classname);
               if ($validatorClass->isSubclassOf(self::$baseValidator))
               {
                  $instance = $validatorClass->newInstance();
               }
            }
         }
      }

      return $instance;
   }

}
