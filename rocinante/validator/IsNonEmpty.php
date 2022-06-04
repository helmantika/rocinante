<?php
namespace rocinante\validator;

require_once 'rocinante/validator/Validator.php';

/**
 * Checks whether a field content is non empty.
 */
class IsNonEmpty extends Validator
{

   /**
    * Creates the validator.
    */
   public function __construct()
   {
      parent::__construct();
      $this->message = self::$l10n->{"validation"}->{"is-non-empty"};
   }

   /**
    * Checks if a field content is right.
    * @param mixed $data A data to validate.
    * @return boolean true if validation passes or false if validation fails.
    */
   protected function check($data)
   {
      return (!empty($data) && !\is_numeric($data)) || \is_numeric($data);
   }

}
