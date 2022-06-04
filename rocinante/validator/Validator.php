<?php

namespace rocinante\validator;

require_once 'rocinante/view/ViewHelper.php';

/**
 * Defines an interface to declare field validators used by the application.
 */
abstract class Validator
{

   /**
    * The message that is shown when validation fails. It should be overriden by subclasses.
    * @var string 
    */
   protected $message = "{{field}}{{param}}";

   /**
    * The extra param for some validators such a IsMinLength or IsMaxLength. It must be set before
    * invoking validate().
    * @var int 
    */
   protected $param = null;

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   protected static $l10n;
   
   /**
    * Creates a new validator.
    */
   public function __construct()
   {
      self::$l10n = \rocinante\view\ViewHelper::instance()->getL10n();
   }
   
   /**
    * Validates the value of a field.
    * @param string $field A field name.
    * @param string $data The field value.
    * @return mixed true whether validation passes, or a string that explains why validation failed.
    */
   public function validate($field, $data)
   {
      $result = true;
      if (!$this->check($data))
      {
         $result = sprintf($this->message, $field, $this->param);
      }
      return $result;
   }

   /**
    * Checks if a field content is right.
    * @param mixed $data A data to validate.
    * @return boolean true whether validation passes or false whether validation fails.
    */
   abstract protected function check($data);

   /**
    * Sets an extra param for some validators such as IsMinLength and IsMaxLength. It must be set 
    * before invoking validate().
    * @param int $param A number.
    */
   public function setParam($param)
   {
      $this->param = $param;
   }

}
