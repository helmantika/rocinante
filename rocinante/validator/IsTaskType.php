<?php

namespace rocinante\validator;

require_once 'rocinante/validator/Validator.php';

/**
 * IsTaskType checks whether a field content belongs to a type of task (TRANSLATION, REVISION, 
 * UPDATING, or GLOSSARY).
 */
class IsTaskType extends Validator
{

   /**
    * Creates the validator.
    */
   public function __construct()
   {
      parent::__construct();
      $this->message = self::$l10n->{"validation"}->{"is-task-type"};
   }

   /**
    * Checks if a field content is right.
    * @param mixed $data A data to validate.
    * @return boolean true if validation passes or false if validation fails.
    */
   protected function check($data)
   {
      return $data === "TRANSLATION" || $data === "REVISION" || $data === "UPDATING" || $data === "GLOSSARY";
   }

}
