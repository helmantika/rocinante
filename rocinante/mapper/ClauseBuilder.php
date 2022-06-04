<?php

namespace rocinante\mapper;

/**
 * ClauseBuilder is a trait that offers methods to create SQL clauses for SELECT statements.
 */
trait ClauseBuilder
{
   /**
    * Builds a WHERE clause from an identity object.
    * @param \rocinante\mapper\identity\Identity $object An identity object.
    * @param string $tableAlias If it's not null, it will be put before the fields.
    * @return array An array with a WHERE clause, types, and values.
    */
   public function buildWhere(\rocinante\mapper\identity\Identity $object, $tableAlias = null)
   {
      $array = array("", "", array());

      if (!$object->isVoid())
      {
         if ($tableAlias !== null)
         {
            $tableAlias .= ".";
         }
         $comparisonStrings = array();
         $values = array();
         
         foreach ($object->getComparisons() as $comparison)
         {
            if ($comparison['logical'])
            {
               $comparisonStrings[] = $comparison['logical'];
            }
            else if ($comparison['placeholder'])
            {
               $comparisonStrings[] = "$tableAlias{$comparison['name']} {$comparison['operator']} ?";
               $values[] = $comparison['value'];
            }
            else
            {
               $comparisonStrings[] = "$tableAlias{$comparison['name']} {$comparison['operator']} {$comparison['value']}";
            }
         }
         
         if (isset($comparisonStrings))
         {
            $where = "WHERE " . \implode(" ", $comparisonStrings);
            $array = array($where, $object->getTypeString(), $values);
         }
      }

      return $array;
   }
}
