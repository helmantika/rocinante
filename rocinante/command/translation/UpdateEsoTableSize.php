<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/translation/MetaTable.php';

/**
 * UpdateEsoTable updates the number of strings of an ESO table or all of them.
 */
class UpdateEsoTableSize extends \rocinante\controller\Command
{

   use \rocinante\command\translation\MetaTable
   {
      readTables as protected;
   }

   /**
    * If 'tableid' property is set, updates the number of strings of the given table, else updates 
    * the number of strings of every ESO table.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/UpdateEsoTableSize")
      {
         $factory = new \rocinante\persistence\PersistenceFactory("EsoTable");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $identity = $factory->getIdentity();
         $tableid = $this->request->getProperty('tableid');
         if ($tableid !== null)
         {
            $identity->field("TableId")->eq($tableid);
         }
         $collection = $assembler->find($identity);
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $size = $this->calculateSize(\intval($object->get('TableId')));
            $object->set('Size', $size);
            $assembler->update($object);
         }
      }
   }

   /**
    * Calculates the number of string of a given table.
    * @param int $tableid A table ID.
    * @return int A number.
    */
   private function calculateSize($tableid)
   {
      if ($tableid === 0)
      {
         $size = $this->calculateLuaSize();
      } 
      else if ($tableid < 0xff)
      {
         $size = $this->calculateMetaTableSize($tableid);
      } 
      else
      {
         $size = $this->calculateLangSize($tableid);
      }
      return $size;
   }

   /**
    * Calculates the number of string of a given table.
    * @return int A number.
    */
   private function calculateLuaSize()
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $counter = new \rocinante\mapper\identity\Identity(array('TextId' => 'i'), "Lua");
      $counter->count("TextId");
      $object = $assembler->find($counter)->first();
      return \intval($object->get('COUNT(TextId)'));
   }

   /**
    * Calculates the number of string of a given table.
    * @param int $tableid A metatable ID.
    * @return int A number.
    */
   private function calculateMetaTableSize($tableid)
   {
      $metaTable = $this->readTables($tableid);

      $factory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);

      // Count rows.
      $counter = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "Lang");
      $counter->count("TableId");
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $counter->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $counter->ior();
         }
      }
      $object = $assembler->find($counter)->first();
      return \intval($object->get('COUNT(TableId)'));
   }

   /**
    * Calculates the number of string of a given table.
    * @param int $tableid A lang table ID.
    * @return int A number.
    */
   private function calculateLangSize($tableid)
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $counter = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "Lang");
      $counter->count("TableId")->field("TableId")->eq($tableid);
      $object = $assembler->find($counter)->first();
      return \intval($object->get('COUNT(TableId)'));
   }

}
