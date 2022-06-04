<?php

namespace rocinante\mapper\identity;

/**
 * Field holds comparison data for each field that will end up in a WHERE clause.
 */
class Field
{

   /**
    * The field name.
    * @var string 
    */
   protected $name = null;

   /**
    * The operator used in the WHERE clause.
    * @var string 
    */
   protected $operator = null;

   /**
    * The array that holds comparasion data.
    * @var array 
    */
   protected $comparisons = array();

   /**
    * The flag that defines whether this field is not ready to be used in a query.
    * @var bool 
    */
   protected $incomplete = false;

   /**
    * Sets up the field name.
    * @param string $name A field name.
    */
   public function __construct($name)
   {
      $this->name = $name;
   }

   /**
    * Adds the operator and the value for the test.
    * @param string $operator An operator used in WHERE clauses.
    * @param mixed $value Any value.
    * @param bool $placeholder If it's false then value is appended to the WHERE clause instead of 
    * using a placeholder (?).
    * @param bool $logical If it's "AND" or "OR", this test and the following one will be linked by
    * means of that argument.
    */
   public function addTest($operator, $value, $placeholder = true, $logical = false)
   {
      $this->comparisons[] = array('name' => $this->name,
                                   'operator' => $operator,
                                   'value' => $value,
                                   'placeholder' => $placeholder,
                                   'logical' => $logical);
   }

   /**
    * Returns an array so that one field can be tested in more than one way.
    */
   public function getComparisons()
   {
      return $this->comparisons;
   }

   /**
    * Defines whether this field is not ready to be used in a query.
    * @return bool true if the field is not ready, otherwise false.
    */
   public function isIncomplete()
   {
      return empty($this->comparisons);
   }

   /**
    * Gets the field name.
    * @return string A field name.
    */
   public function getName()
   {
      return $this->name;
   }

}
