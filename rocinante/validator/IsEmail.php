<?php

namespace rocinante\validator;

require_once 'rocinante/validator/Validator.php';

/**
 * Checks whether a field content is an electronic mail address.
 */
class IsEmail extends Validator
{

   /**
    * Creates the validator.
    */
   public function __construct()
   {
      parent::__construct();
      $this->message = self::$l10n->{"validation"}->{"is-email"};
   }

   /**
    * Checks if a field content is right.
    * @param mixed $data A data to validate.
    * @return boolean true if validation passes or false if validation fails.
    */
   protected function check($data)
   {
      $pattern = '/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i';
      return \preg_match($pattern, $data) === 1;
   }

}
