<?php

namespace rocinante\persistence;

require_once 'rocinante/persistence/SqlManager.php';

/**
 * Mapper defines an interface to connect a domain object to a database table. Mappers should be 
 * created for simple domain objects, while creating complex ones is the persistence factory job.
 */
abstract class Mapper
{

   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager 
    */
   protected static $sqlm;

   /**
    * Creates a new mapper setting the core functionality for making database requests.
    */
   public function __construct()
   {
      if (!isset(self::$sqlm))
      {
         self::$sqlm = \rocinante\persistence\SqlManager::instance();
      }
   }

   /**
    * Performs a SELECT query.
    */
   abstract public function select(array $values = null);

   /**
    * Performs an INSERT query.
    */
   abstract public function insert(\rocinante\domain\Domain $object);

   /**
    * Performs an UPDATE query.
    */
   abstract public function update(\rocinante\domain\Domain $object);
}
