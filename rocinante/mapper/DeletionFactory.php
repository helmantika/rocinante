<?php

namespace rocinante\mapper;

require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/mapper/ClauseBuilder.php';

/**
 * DeletionFactory acquires the infomation necessary from an identity object to build a prepared 
 * DELETE statement.
 */
class DeletionFactory
{

   use \rocinante\mapper\ClauseBuilder
   {
      buildWhere as protected;
   }

   /**
    * Build a DELETE statement from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @param array $tables The name of the tables whose rows will be deleted. If null then table 
    * of the identity object will be used.
    * @return array An array with four elements: selected field names, the statement, types, and 
    * values.
    */
   public function delete(\rocinante\mapper\identity\Identity $object, $tables = null)
   {
      return $object->hasJoins() ? $this->join($object, $tables) : $this->nojoin($object);
   }

   /**
    * Build a simple DELETE statement from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @return array An array with three elements: the statement, types, and values.
    */
   protected function nojoin(\rocinante\mapper\identity\Identity $object)
   {
      $core = "DELETE FROM {$object->getTable()}";
      list($where, $types, $values) = $this->buildWhere($object);

      return array($core . " " . $where, $types, $values);
   }

   /**
    * Build a complex DELETE statement (with JOINs) from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @param array $tables The name of the tables whose rows will be deleted. If null then table 
    * of the identity object will be used.
    * @return array An array with three elements: the statement, types, and values.
    */
   protected function join(\rocinante\mapper\identity\Identity $object, $tables)
   {
      $core = null;

      // Get selected fields for the first object. 
      $fields = $object->getQualifiedObjectFields();
      // Build WHERE clause for the first object.
      list($where, $types, $values) = $this->buildWhere($object, $object->getTable());
      $lastTable = $object->getTable();

      // For the other objects...
      foreach ($object->getJoins() as $join)
      {
         // Get selected fields.
         $newObject = $join['identity'];
         $thisTable = $newObject->getTable();
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
         list($newWhere, $newTypes, $newValues) = $this->buildWhere($newObject, $newObject->getTable());
         if (isset($where) && isset($newWhere))
         {
            $newWhere = \str_replace('WHERE', ' AND', $newWhere);
         }
         $where .= $newWhere;
         $types .= $newTypes;
         $values = \array_merge($values, $newValues);
         // This table will be the last one for next iteration.
         $lastTable = $newObject->getTable();
      }

      // Build the statement.
      $core = "DELETE " . \implode(", ", $tables) . " FROM {$object->getTable()}" . $core;

      return array($core . " " . $where, $types, $values);
   }

}
