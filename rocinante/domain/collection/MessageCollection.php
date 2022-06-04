<?php

namespace rocinante\domain\collection;

require_once 'rocinante/domain/collection/Collection.php';

/**
 * MessageCollection is a Message domain object group.
 */
class MessageCollection extends Collection
{

   /**
    * Returns the domain class this collection has.
    */
   protected function targetClass()
   {
      return "\\rocinante\\domain\\model\\Message";
   }

}