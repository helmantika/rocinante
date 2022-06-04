<?php

namespace rocinante\validator;

require_once 'rocinante/validator/Validator.php';

/**
 * Checks whether a field content length is greater or equal than a given value.
 */
class IsMinLength extends Validator
{

   /**
    * Creates the validator.
    */
   public function __construct()
   {
      parent::__construct();
      $this->message = self::$l10n->{"validation"}->{"is-min-length"};
   }

   /**
    * Checks if a field content is right.
    * @param mixed $data A data to validate.
    * @return boolean true if validation passes or false if validation fails.
    */
   protected function check($data)
   {
      return \strlen($data) >= $this->param;
   }

}
