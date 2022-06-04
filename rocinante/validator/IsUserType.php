<?php

namespace rocinante\validator;

require_once 'rocinante/validator/Validator.php';

/**
 * IsUserType checks whether a field content belongs to a user category (ADMIN, ADVISOR, or 
 * TRANSLATOR).
 */
class IsUserType extends Validator
{

   /**
    * Creates the validator.
    */
   public function __construct()
   {
      parent::__construct();
      $this->message = self::$l10n->{"validation"}->{"is-user-type"};
   }

   /**
    * Checks if a field content is right.
    * @param mixed $data A data to validate.
    * @return boolean true if validation passes or false if validation fails.
    */
   protected function check($data)
   {
      return $data === "TRANSLATOR" || $data === "ADVISOR" || $data === "ADMIN";
   }

}
