<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * MetaTable is a domain class that represents those tables that are formed by another ones.
 */
class MetaTable extends Domain
{

   /**
    * Creates a new metatable.
    */
   public function __construct()
   {
      $fields = array('MetaTableId' => 'i',
                      'Seq' => 'i',
                      'TableId' => 'i');
      parent::__construct($fields);
   }

}
