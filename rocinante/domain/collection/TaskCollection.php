<?php

namespace rocinante\domain\collection;

require_once 'rocinante/domain/collection/Collection.php';

/**
 * TaskCollection is an Task domain object group.
 */
class TaskCollection extends Collection
{

   /**
    * Returns the domain class this collection has.
    */
   protected function targetClass()
   {
      return "\\rocinante\\domain\\model\\Task";
   }

}
