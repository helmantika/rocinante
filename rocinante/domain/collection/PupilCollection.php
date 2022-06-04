<?php

namespace rocinante\domain\collection;

require_once 'rocinante/domain/collection/Collection.php';

/**
 * PupilCollection is a Pupil domain object group.
 */
class PupilCollection extends Collection
{

   /**
    * Returns the domain class this collection has.
    */
   protected function targetClass()
   {
      return "\\rocinante\\domain\\model\\Pupil";
   }

}
