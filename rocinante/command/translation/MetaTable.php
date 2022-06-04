<?php

namespace rocinante\command\translation;

require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * Metatable is a trait that offers methods to manage metatables.
 */
trait MetaTable
{

   /**
    * Reads which tables are part of a given metatable.
    * @param int $metatableid Metatable ID.
    * @return array An array where keys are numbers that defines the order of the tables, and values
    * are table IDs.
    */
   public function readTables($metatableid)
   {
      $metaTable = array();
      $factory = new \rocinante\persistence\PersistenceFactory("MetaTable");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $identity->field("MetaTableId")->eq($metatableid);
      $collection = $assembler->find($identity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $metaTable[$object->get('Seq')] = $object->get('TableId');
      }
      \ksort($metaTable);
      return $metaTable;
   }

   /**
    * Returns the metatable ID that a given table is part of.
    * @param int $tableid A lang table ID.
    * @return int A metatable ID if the table is part of a metatable, or 0 if the table is not part 
    * of a metatable.
    */
   public function getMetaTable($tableid)
   {
      $metatableid = 0;
      $factory = new \rocinante\persistence\PersistenceFactory("MetaTable");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $identity->field("TableId")->eq($tableid);
      $collection = $assembler->find($identity);
      $metatable = $collection->first();
      if ($metatable !== null)
      {
         $metatableid = $metatable->get('MetaTableId');
      }
      return $metatableid;
   }
   
   /**
    * Reads which tables are part of any metatable.
    * @return array An array where values are table IDs that are part of a metatable.
    */
   public function readEveryTable()
   {
      $tables = array();
      $factory = new \rocinante\persistence\PersistenceFactory("MetaTable");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $collection = $assembler->find($identity);
      $generator = $collection->getGenerator();
      foreach ($generator as $object)
      {
         $tables[] = $object->get('TableId');
      }
      return $tables;
   }
   
}
