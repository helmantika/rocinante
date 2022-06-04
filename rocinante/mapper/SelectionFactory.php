<?php

namespace rocinante\mapper;

require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/mapper/ClauseBuilder.php';

/**
 * SelectionFactory acquires the infomation necessary from an identity object to build a prepared 
 * SELECT statement.
 */
class SelectionFactory
{

   use \rocinante\mapper\ClauseBuilder { buildWhere as protected; }
   
   /**
    * Build a SELECT statement from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @return array An array with four elements: selected field names, the statement, types, and 
    * values.
    */
   public function select(\rocinante\mapper\identity\Identity $object)
   {
      return $object->hasJoins() ? $this->join($object) : $this->nojoin($object);
   }

   /**
    * Build a simple SELECT clause from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @return array An array with four elements: selected field names, the statement, types, and 
    * values.
    */
   protected function nojoin(\rocinante\mapper\identity\Identity $object)
   {
      // Main statement.
      $fields = \implode(',', $object->getObjectFields());
      $tablename = $object->getTable();
      if ($object->getAlias() !== null)
      {
         $tablename .= " AS " . $object->getAlias();
      }
      $core = "SELECT {$object->getDistinct()} $fields FROM $tablename";
      // WHERE clause.
      list($where, $types, $values) = $this->buildWhere($object, $object->getAlias());
      // ORDER BY clause.
      $order = $this->buildOrderBy($object, $object->getAlias());
      // LIMIT clause.
      list($limit, $ltypes, $lvalues) = $this->buildLimit($object);
      
      return array($object->getObjectFields(),
                   $core . " " . $where . " " . $order . " " . $limit,
                   $types . $ltypes,
                   \array_merge($values, $lvalues));
   }

   /**
    * Build a complex SELECT clause (with JOINs) from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @return array An array with four elements: selected field names, the statement, types, and 
    * values.
    */
   protected function join(\rocinante\mapper\identity\Identity $object)
   {
      $core = null;

      // Get selected fields for the first object. 
      $fields = $object->getQualifiedObjectFields();
      // Build WHERE clause for the first object.
      $tablename = $object->getTable();
      if ($object->getAlias() !== null)
      {
         $tablename .= " AS " . $object->getAlias();
      }
      list($where, $types, $values) = $this->buildWhere($object, ($object->getAlias() === null ? $object->getTable() : $object->getAlias()));
      // Build ORDER BY clause for the first object.
      $order = $this->buildOrderBy($object, ($object->getAlias() === null ? $object->getTable() : $object->getAlias()));
      // Build LIMIT clause.
      list($limit, $ltypes, $lvalues) = $this->buildLimit($object);
      $lastTable = ($object->getAlias() === null ? $object->getTable() : $object->getAlias());
      
      // For the other objects...
      foreach ($object->getJoins() as $join)
      {
         // Get selected fields.
         $newObject = $join['identity'];
         $thisTable = $newObject->getTable();
         if ($newObject->getAlias() !== null)
         {
            $thisTable .= " AS " . $newObject->getAlias();
         }
         $newFields = $newObject->getQualifiedObjectFields();
         
         // Merge all the selected fields.
         $fields = \array_merge($fields, $newFields);
         
         // Build JOIN part of the statement.
         if (!\is_array($join['field1']) && !\is_array($join['field2'])) 
         {
            $joinField1 = \strpos($join['field1'], '.') !== false ? $join['field1'] : $lastTable . "." . $join['field1'];
            $joinField2 = \strpos($join['field2'], '.') !== false ? $join['field2'] : $thisTable . "." . $join['field2'];
            $core .= "{$join['type']} JOIN $thisTable ON $joinField1=$joinField2";
         }
         else
         {
            \array_walk($join['field1'], function (&$value, $key, $table) { $value = \strpos($value, '.') !== false ? $value : $table . "." . $value; }, $lastTable);
            \array_walk($join['field2'], function (&$value, $key, $table) { $value = \strpos($value, '.') !== false ? $value : $table . "." . $value; }, $thisTable);
            for ($i = 0; $i < \count($join['field1']); $i++)
            {
               $joinFields[] = "{$join['field1'][$i]}={$join['field2'][$i]}";
            }
            $core .= "{$join['type']} JOIN $thisTable ON " . \implode(" AND ", $joinFields);
         }
         
         // Merge WHERE clauses.
         list($newWhere, $newTypes, $newValues) = $this->buildWhere($newObject, ($newObject->getAlias() === null ? $newObject->getTable() : $newObject->getAlias()));
         if (!empty($where) && !empty($newWhere))
         {
            $newWhere = \str_replace('WHERE', ' AND', $newWhere);
         }
         $where .= $newWhere;
         $types .= $newTypes;
         $values = \array_merge($values, $newValues);
         
         // Merge ORDER BY clauses.
         $newOrder = $this->buildOrderBy($newObject, ($newObject->getAlias() === null ? $newObject->getTable() : $newObject->getAlias()));
         if (!empty($order) && !empty($newOrder))
         {
            $newOrder = \str_replace('ORDER BY', ',', $newOrder);
         }
         $order .= $newOrder;
         
         // This table will be the last one for next iteration.
         $lastTable = ($newObject->getAlias() === null ? $newObject->getTable() : $newObject->getAlias());
      }

      // Build the statement.
      $fieldString = \implode(',', $fields);
      $core = "SELECT {$object->getDistinct()} $fieldString FROM $tablename" . $core;

      return array($fields, $core . " " . $where . " " . $order . " " . $limit, 
                   $types . $ltypes,
                   \array_merge($values, $lvalues));
   }

   /**
    * Builds a ORDER BY clause from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @param string $tableAlias If it's not null, it will be put before the fields.
    * @return string A string with an ORDER BY clause.
    */
   protected function buildOrderBy(\rocinante\mapper\identity\Identity $object, $tableAlias = null)
   {
      $string = null;
      list($type, $fields) = $object->getOrderBy();
      if (\count($fields) > 0)
      {
         if ($tableAlias !== null)
         {
            $tableAlias .= ".";
         }
         $qfields = array();
         foreach ($fields as $field)
         {
            if (\substr($field, 0, 5) !== "FIELD")
            {
               $qfields[] = "$tableAlias$field";
            }
            else
            {
               $qfields[] = "FIELD($tableAlias" . \substr($field, 6);
            }
         }
         $string = "ORDER BY " . \implode(",", $qfields) . " " . $type;
      }

      return $string;
   }
   
   /**
    * Builds a LIMIT clause.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @return array An array with a WHERE clause, types, and values.
    */
   protected function buildLimit(\rocinante\mapper\identity\Identity $object)
   {
      return $object->getLimit();
   }
}
