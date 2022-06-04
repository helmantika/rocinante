<?php

namespace rocinante\domain\collection;

require_once 'rocinante/domain/collection/Collection.php';

/**
 * StatsCollection is a Stats domain object group.
 */
class StatsCollection extends Collection
{

   /**
    * Returns the domain class this collection has.
    */
   protected function targetClass()
   {
      return "\\rocinante\\domain\\model\\Stats";
   }

}