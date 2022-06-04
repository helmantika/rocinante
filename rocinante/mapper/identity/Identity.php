<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Field.php';

/**
 * Identity encapsulates the conditional aspect of a database query in such way that different 
 * combinations can be combined at runtime.
 */
class Identity
{

   /**
    * The field names to which the query is constrained.
    * @var array 
    */
   private $enforce = array();
   
   /**
    * The database table name that is bound the identity object.
    * @var string 
    */
   protected $table = null;
   
   /**
    * The current field.
    * @var Field 
    */
   protected $currentField = null;

   /**
    * The identity object fields that are part of the WHERE clause.
    * @var array 
    */
   protected $fields = array();
   
   /**
    * The string that contains one or more characters which specify the types for the corresponding 
    * bind variables: i (integer), d (double), s (string), and b (blob).
    * @var string 
    */
   protected $typeString = "";
   
   /**
    * The fields for an ORDER BY clause.
    * @var string 
    */
   protected $orderBy = array("", array());
   
   /**
    * The LIMIT clause defined by two strings (LIMIT and types), a number that defines the initial 
    * row, and an optional number that defines the number of rows.
    * @var array 
    */
   protected $limit = array("", "", array());
   
   /**
    * The identity objects that will be join this one, and the fields that relate them.
    * @var array
    */
   protected $joins = array();
   
   /**
    * Defines whether SELECT statement is DISTINCT.
    * @var mixed null or "DISTINCT". 
    */
   protected $distinct = null;
   
   /**
    * The alias for the table this identity object is bound.
    * @var mixed null or a string.
    */
   protected $alias = null;
   
   /**
    * The field name used to count rows.
    * @var string 
    */
   protected $counter = null;
   
   /**
    * An identity object can start off empty, or with a field.
    * @param array $enforce Field names and their types to which the query is constrained.
    * @param string $table A database table name that is bound the identity object.
    * @param string $field A field name to test.
    */
   public function __construct($enforce, $table, $field = null)
   {
      $this->enforce = $enforce;
      $this->table = $table;
      if (!\is_null($field))
      {
         $this->field($field);
      }
   }

   /**
    * Sets a field up.
    * @param string $fieldName A field name.
    * @return \rocinante\mapper\Identity A reference to the current object allowing fluent syntax.
    * @throws \Exception Current field is incomplete.
    */
   public function field($fieldName)
   {
      if (!$this->isVoid() && $this->currentField->isIncomplete())
      {
         throw new \Exception("$fieldName is an incomplete field");
      }
      $this->enforceField($fieldName);
      $this->typeString .= $this->enforce[$fieldName];
      if (isset($this->fields[$fieldName]))
      {
         $this->currentField = $this->fields[$fieldName];
      } 
      else
      {
         $this->currentField = new \rocinante\mapper\identity\Field($fieldName);
         $this->fields[$fieldName] = $this->currentField;
      }

      return $this;
   }
   
   /**
    * Checks whether a field name is legal.
    * @param string $fieldName A field name.
    * @throws \Exception Field is not a legal field.
    */
   public function enforceField($fieldName)
   {
      if (!\array_key_exists($fieldName, $this->enforce) && !empty($this->enforce))
      {
         $forceList = \implode(', ', \array_keys($this->enforce));
         throw new \Exception("$fieldName is not a legal field ($forceList)");
      }
   }
   
   /**
    * Joins this identity object and another one. In other words, a SELECT ... JOIN query will be 
    * created. 
    * @param \rocinante\mapper\Identity $object Another identity object that will be joined.
    * @param type $fieldName1 If a table is not specified, it defines a field name of this identity 
    * object. However, if a table is specified, the field name will be taken as is. Anyway, it will 
    * be used as the first operand of the ON clause. 
    * @param type $fieldName2 If a table is not specified, it defines a field name of the other identity 
    * object. However, if a table is specified, the field name will be taken as is. Anyway, it will 
    * be used as the second operand of the ON clause. 
    * @return \rocinante\mapper\identity\Identity
    */
   public function join(Identity $object, $fieldName1, $fieldName2)
   {
      $this->joins[] = array('type' => '', 'identity' => $object, 'field1' => $fieldName1, 'field2' => $fieldName2);
      return $this;
   }
   
   /**
    * Joins this identity object and another one by means of several fields. In other words, a 
    * SELECT ... JOIN table ON field1 = field1 AND field2 = field2 ... query will be created. 
    * @param \rocinante\mapper\Identity $object Another identity object that will be joined.
    * @param type $fieldNames1 If a table is not specified, it defines fields name of this identity 
    * object. However, if a table is specified, field names will be taken as is. Anyway, it will 
    * be used as the first operand of the ON clause. 
    * @param type $fieldNames2 If a table is not specified, it defines a field names of the other 
    * identity object. However, if a table is specified, field names will be taken as is. Anyway, 
    * it will be used as the second operand of the ON clause. 
    * @return \rocinante\mapper\identity\Identity
    */
   public function multipleJoin(Identity $object, array $fieldNames1, array $fieldNames2)
   {
      $this->joins[] = array('type' => '', 'identity' => $object, 'field1' => $fieldNames1, 'field2' => $fieldNames2);
      return $this;
   }
   
   /**
    * Joins this identity object and another one. In other words, a SELECT ... LEFT JOIN query will 
    * be created. 
    * @param \rocinante\mapper\Identity $object Another identity object that will be joined.
    * @param type $fieldName1 If a table is not specified, it defines a field name of this identity 
    * object. However, if a table is specified, the field name will be taken as is. Anyway, it will 
    * be used as the first operand of the ON clause. 
    * @param type $fieldName2 If a table is not specified, it defines a field name of the other identity 
    * object. However, if a table is specified, the field name will be taken as is. Anyway, it will 
    * be used as the second operand of the ON clause. 
    * @return \rocinante\mapper\identity\Identity
    */
   public function leftJoin(Identity $object, $fieldName1, $fieldName2)
   {
      $this->joins[] = array('type' => ' LEFT', 'identity' => $object, 'field1' => $fieldName1, 'field2' => $fieldName2);
      return $this;
   }

   /**
    * Adds an equility operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function eq($value)
   {
      return $this->operator('=', $value);
   }

   /**
    * Adds a non-equality operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function neq($value)
   {
      return $this->operator('<>', $value);
   }

   /**
    * Adds a less than operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function lt($value)
   {
      return $this->operator('<', $value);
   }

   /**
    * Adds a less than or equal to operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function le($value)
   {
      return $this->operator('<=', $value);
   }

   /**
    * Adds a greater than operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function gt($value)
   {
      return $this->operator('>', $value);
   }

   /**
    * Adds a greater than or equal to operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function ge($value)
   {
      return $this->operator('>=', $value);
   }

   /**
    * Adds an IN operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function in($value)
   {
      return $this->operator('IN', $value, false);
   }
   
   /**
    * Adds an NOT IN operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function nin($value)
   {
      return $this->operator('NOT IN', $value, false);
   }
   
   /**
    * Adds an IS NULL operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function eqNull()
   {
      return $this->operator('IS NULL', null, false);
   }
   
   /**
    * Adds an IS NOT NULL operator to the current field.
    * @param mixed $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function neqNull()
   {
      return $this->operator('IS NOT NULL', null, false);
   }
   
   /**
    * Adds a REGEXP operator to the current field.
    * @param string $value Any suitable value for this operator.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function regexp($value)
   {
      return $this->operator('REGEXP', $value, false);
   }
   
   /**
    * Adds the logical operator AND to the current clause.
    */
   public function iand()
   {
      return $this->operator('', null, false, "AND");
   }
   
   /**
    * Adds the logical operator OR to the current clause.
    */
   public function ior()
   {
      return $this->operator('', null, false, "OR");
   }
   
   /**
    * Adds a left parenthesis to the current clause.
    */
   public function lparen()
   {
      return $this->operator('', null, false, "(");
   }
   
   /**
    * Adds a right parenthesis to the current clause.
    */
   public function rparen()
   {
      return $this->operator('', null, false, ")");
   }
   
   /**
    * Changes the statement into a SELECT COUNT(...) statement.
    * @param string $fieldName A field name.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    * @throws \Exception Field name is not defined.
    */
   public function count($fieldName)
   {
      $this->enforceField($fieldName);
      $this->counter = $fieldName;
      return $this;
   }
   
   /**
    * Adds an ORDER BY ascending clause.
    * @param string $fieldName The name of the field that will be used to sort data.
    * @throws \Exception There isn't an identity object defined.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function orderByAsc($fieldName)
   {
      $this->enforceField($fieldName);
      $this->orderBy[0] = "ASC";
      \array_push($this->orderBy[1], $fieldName);
      return $this;
   }
   
   /**
    * Adds an ORDER BY ascending clause.
    * @param string $fieldName The name of the field that will be used to sort data.
    * @param array $values
    * @throws \Exception There isn't an identity object defined.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function orderByFieldAsc($fieldName, array $values)
   {
      $this->enforceField($fieldName);
      $this->orderBy[0] = "ASC";
      \array_push($this->orderBy[1], "FIELD($fieldName," . \implode(",", $values) . ")");
      return $this;
   }
   
   /**
    * Adds an ORDER BY descending clause.
    * @param string $fieldName The name of the field that will be used to sort data.
    * @throws \Exception There isn't an identity object defined.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function orderByDesc($fieldName)
   {
      $this->enforceField($fieldName);
      $this->orderBy[0] = "DESC";
      \array_push($this->orderBy[1], $fieldName);
      return $this;
   }
   
   /**
    * Adds a LIMIT clause that is used to constrain the number of rows returned by the statement.
    * @param int $first The offset of the first row to return. The offset of the initial row is 0.
    * @param int $count The maximum number of rows to return.
    * @throws \Exception There isn't an identity object defined, and Limits are not numerical.
    */
   public function limit($first, $count = PHP_INT_MAX)
   {
      if (!\is_integer($first) || !\is_integer($count))
      {
         throw new \Exception("Limits are not numbers");
      }
      
      if ($count !== PHP_INT_MAX)
      {
         $this->limit = array("LIMIT ?,?", "ii", array($first, $count));
      }
      else
      {
         $this->limit = array("LIMIT ?", "i", array($first));
      }  
   }
   
   /**
    * Sets the query as DISTINCT.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */   
   public function distinct()
   {
      $this->distinct = "DISTINCT";
      return $this;
   }
   
   /**
    * Sets an alias for the table this identity object is bound.
    * @param string $alias
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    */
   public function alias($alias)
   {
      $this->alias = $alias;
      return $this;
   }
   
   /**
    * Adds an operator and a test value to the current field.
    * @param string $symbol A comparison symbol used in WHERE clauses.
    * @param mixed $value Any suitable value for this operator.
    * @param bool $placeholder If it's false then value is appended to the WHERE clause 
    * instead of using a placeholder (?).
    * @param bool $logical If it's "AND" or "OR", this test and the following one will be linked by
    * means of that argument.
    * @return \rocinante\mapper\identity\Identity A reference to the current object.
    * @throws \Exception No object field defined.
    */
   private function operator($symbol, $value, $placeholder = true, $logical = false)
   {
      if ($this->isVoid())
      {
         throw new \Exception("No object field defined");
      }
      // Remove the type of the field if a placeholder is not needed.
      if (!$placeholder && !$logical)
      {
         $this->typeString = \substr($this->typeString, 0, -1);
      }
      $this->currentField->addTest($symbol, $value, $placeholder, $logical);
      return $this;
   }

   /**
    * Defines whether the identity object doesn't have any fields yet. 
    * @return bool true whether the identity object doesn't have any fields, otherwise false.
    */
   public function isVoid()
   {
      return empty($this->fields);
   }
   
   /**
    * Gets all comparisons built up so far in an associative array.
    * @return array An array composed by associative arrays whose keys are 'name', 'operator', and 
    * 'value'.
    */
   public function getComparisons()
   {
      $comparisons = array();
      foreach ($this->fields as $field)
      {
         $comparisons = \array_merge($comparisons, $field->getComparisons());
      }
      return $comparisons;
   }

   /**
    * Gets the field names to which this is constrained.
    * @return array A field names.
    */
   public function getObjectFields()
   {
      return $this->counter === null ? \array_keys($this->enforce) : array("COUNT($this->counter)");
   }
   
   /**
    * Gets the qualified field names to which this is constrained. A qualified field name is formed 
    * by a table name, a dot, and a field name.
    * @return array A qualified field names.
    */
   public function getQualifiedObjectFields()
   {
      $result = null;
      $tablename = ($this->alias === null ? $this->table : $this->alias);
      if ($this->counter === null)
      {
         $qfields = array();
         foreach (\array_keys($this->enforce) as $field)
         {
            $qfields[] = "$tablename.$field";
         }
         $result = $qfields;
      }
      else
      {
         $result = array("COUNT($tablename.$this->counter)");
      }
      return $result;
   }
   
   /**
    * Gets a string that contains one or more characters which specify the types for the  
    * corresponding bind variables: i (integer), d (double), s (string), and b (blob).
    * @return string A string like "isssdi".
    */
   public function getTypeString()
   {
      return $this->typeString;
   }
   
   /**
    * Defines whether the query has JOINs.
    */
   public function hasJoins()
   {
      return \count($this->joins) > 0;
   }
   
   /**
    * Gets an array that contents JOIN information.
    * @return array An array that contents arrays with this elements: an identity object to be 
    * joined, and two field names for "ON f1 = f2" clause.
    */
   public function getJoins()
   {
      return $this->joins;
   }

   /**
    * Gets the database table name that is bound the identity object.
    * @return string A table name.
    */
   public function getTable()
   {
      return $this->table;
   }
   /**
    * Gets the ORDER BY clause.
    * @return string A string or null.
    */
   public function getOrderBy()
   {
      return $this->orderBy;
   }
 
   /**
    * Gets the LIMIT clause.
    * @return array An array with two strings (LIMIT and types), a number that defines the initial 
    * row, and an optional number that defines the number of rows. Example: ["LIMIT ?,?", "ii", [3,6]]
    */
   public function getLimit()
   {
      return $this->limit;
   }
   
   /**
    * Returns true whether SELECT statement is DISTINCT.
    * @return string DISTINCT or null. 
    */
   public function getDistinct()
   {
      return $this->distinct;
   }
   
   /**
    * Returns the alias for the table this identity object is bound.
    * @return mixed null or a string.
    */
   public function getAlias()
   {
      return $this->alias;
   }
   
   /**
    * Returns true whether SELECT statement is a SELECT COUNT(...) one.
    */
   public function isCounter()
   {
      return $this->counter !== null;
   }
}
