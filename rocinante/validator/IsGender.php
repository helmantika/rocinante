<?php

namespace rocinante\validator;

require_once 'rocinante/validator/Validator.php';

/**
 * IsGender checks whether a field content belongs to a person sex (MALE, or FEMALE).
 */
class IsGender extends Validator
{

   /**
    * Creates the validator.
    */
   public function __construct()
   {
      parent::__construct();
      $this->message = self::$l10n->{"validation"}->{"is-gender"};
   }

   /**
    * Checks if a field content is right.
    * @param mixed $data A data to validate.
    * @return boolean true if validation passes or false if validation fails.
    */
   protected function check($data)
   {
      return $data === "MALE" || $data === "FEMALE";
   }

}
