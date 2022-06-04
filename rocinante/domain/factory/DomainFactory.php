<?php

namespace rocinante\domain\factory;

/**
 * DomainFactory defines an interface to create domain objects.
 */
interface DomainFactory
{

   /**
    * Creates a domain object.
    * @param array $array Raw data to create the object.
    */
   public function createObject(array $array);
}
