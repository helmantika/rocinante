<?php

namespace rocinante\mapper\update;

/**
 * UpdateFactory defines an interface to acquire the infomation necessary to build prepared UPDATE
 * or INSERT statements.
 */
abstract class UpdateFactory
{

   /**
    * Inserts or updates a domain object.
    */
   abstract public function update(\rocinante\domain\model\Domain $object);

   /**
    * Inserts a domain object. If this method is not overriden, update process will be followed.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      return $this->update($object);
   }
   
   /**
    * Builds an UPDATE or INSERT statement.
    * @param type $table A database table name.
    * @param array $fields Field names to insert.
    * @param array $types Types of the fields.
    * @param array $conditions Conditions for a WHERE clause (only on UPDATE).
    * @return array An array with three elements: the first one is the statement, the second one is 
    * a string with the types of the fields, and the last one is an array of terms. Terms are values
    * for a WHERE clause (on UPDATE), or values that will be add (on INSERT).
    */
   protected function buildStatement($table, array $fields, array $types, array $conditions = null)
   {
      $terms = array();
      $typeString = null;

      if (!\is_null($conditions))
      {
         list($query, $typeString, $terms) = $this->buildUpdateStmt($table, $fields, $types, $conditions);
      } 
      else
      {
         list($query, $typeString, $terms) = $this->buildInsertStmt($table, $fields, $types);
      }

      return array($query, $typeString, $terms);
   }

   /**
    * Builds an UPDATE statement.
    * @param type $table A database table name.
    * @param array $fields Field names to insert.
    * @param array $types Types of the fields.
    * @param array $conditions Conditions for a WHERE clause.
    * @return array An array with three elements: the first one is the statement, the second one is 
    * a string with the types of the fields, and the last one is an array of terms. Terms are values
    * for a WHERE clause.
    */
   protected function buildUpdateStmt($table, array $fields, array $types, array $conditions)
   {
      $typeString = null;
      $query = "UPDATE {$table} SET ";
      foreach ($fields as $name => $value)
      {
         if ($value !== null)
         {
            if ($value !== "NULL")
            {
               $query .= $name . ' = ?,';
               $typeString .= $types[$name];
               $terms[] = $value;
            }
            else
            {
               $query .= $name . ' = NULL,';
            }
         }
      }

      $cond = array();
      $query = \rtrim($query, ',') . " WHERE ";
      foreach ($conditions as $key => $value)
      {
         $cond[] = "$key = ?";
         $terms[] = $value;
         $typeString .= $types[$key];
      }
      $query .= \implode(" AND ", $cond);
      
      return array($query, $typeString, $terms);
   }

   /**
    * Builds an INSERT statement.
    * @param type $table A database table name.
    * @param array $fields Field names to insert.
    * @param array $types Types of the fields.
    * @return array An array with three elements: the first one is the statement, the second one is 
    * a string with the types of the fields, and the last one is an array of terms. Terms are values
    * that will be add by means of the statement.
    */
   protected function buildInsertStmt($table, array $fields, array $types)
   {
      $query = "INSERT INTO {$table} (";
      foreach ($fields as $name => $value)
      {
         if ($value !== null)
         {
            $query .= $name . ',';
         }
      }
      $query = \rtrim($query, ',') . ") VALUES (";
      $typeString = null;
      foreach ($fields as $name => $value)
      {
         if ($value !== null)
         {
            $terms[] = $value;
            $qs[] = '?';
            $typeString .= $types[$name];
         }
      }
      $query .= \implode(",", $qs);
      $query .= ")";
      
      return array($query, $typeString, $terms);
   }

}
