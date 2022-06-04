<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * LangType is a domain class that stores an ESO table type. Types are used to identify contents of 
 * a table by means of a color. This feature is useful in metatables.
 */
class LangType extends Domain
{

   /**
    * Creates a new translation status.
    */
   public function __construct()
   {
      $fields = array('TypeId' => 'i',
                      'Description' => 's',
                      'Color' => 's');
      parent::__construct($fields);
   }

}
