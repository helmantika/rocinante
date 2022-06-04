<?php

namespace rocinante\domain\collection;

require_once 'rocinante/domain/collection/Collection.php';

/**
 * GlossaryCollection is a Glossary domain object group.
 */
class GlossaryCollection extends Collection
{

   /**
    * Returns the domain class this collection has.
    */
   protected function targetClass()
   {
      return "\\rocinante\\domain\\model\\Glossary";
   }

}
