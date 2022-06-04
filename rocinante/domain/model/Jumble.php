<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Jumble is a special domain class that stores results of queries with JOINs. It is not constrained
 * by a field set, and it can't be used as a parameter of update and deletion factories.
 */
class Jumble extends Domain
{

   /**
    * A jumble object doesn't have any field initially.
    */
   public function __construct()
   {
      parent::__construct(array());
   }

   /**
    * Sets a value for a field name. The field name won't be check.
    * @param string $field A field name.
    * @param mixed $value A field value.
    */
   public function set($field, $value)
   {
      $this->fields[$field] = $value;
   }

}
