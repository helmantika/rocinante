<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/translation/MetaTable.php';

/**
 * UpdateEsoTablePercentages updates percentage of translated strings and percentage of revised 
 * strings of an ESO table or all of them.
 */
class UpdateEsoTablePercentages extends \rocinante\controller\Command
{

   use \rocinante\command\translation\MetaTable
   {
      readTables as protected;
   }

   /**
    * If 'tableid' property is set, updates percentage of translated strings and percentage of 
    * revised strings of the given table, else updates percentages of every ESO table.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/UpdateEsoTablePercentages")
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
            list($translated, $revised) = $this->calculatePercentages(\intval($object->get('TableId')));
            $object->set('Translated', $translated);
            $object->set('Revised', $revised);
            $assembler->update($object);
         }
      }
   }

   /**
    * Calculates percentages of a given table.
    * @param int $tableid A table ID.
    * @return int A number.
    */
   private function calculatePercentages($tableid)
   {
      if ($tableid === 0)
      {
         list($translated, $revised) = $this->calculateLuaPercentages();
      } 
      else if ($tableid < 0xff)
      {
         list($translated, $revised) = $this->calculateMetaTablePercentages($tableid);
      } 
      else
      {
         list($translated, $revised) = $this->calculateLangPercentages($tableid);
      }
      return array($translated, $revised);
   }

   /**
    * Calculates percentages of Lua table.
    * @return int A number.
    */
   private function calculateLuaPercentages()
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      
      $counter1 = new \rocinante\mapper\identity\Identity(array('TextId' => 's'), "Lua");
      $counter1->count("TextId");
      $object1 = $assembler->find($counter1)->first();
      $size = \doubleval($object1->get('COUNT(TextId)'));
      
      $counter2 = new \rocinante\mapper\identity\Identity(array('TextId' => 's', 'IsTranslated' => 'i'), "Lua");
      $counter2->count("TextId")->field("IsTranslated")->eq(1);
      $object2 = $assembler->find($counter2)->first();
      $translated = \doubleval($object2->get('COUNT(TextId)'));
      
      $counter3 = new \rocinante\mapper\identity\Identity(array('TextId' => 's', 'IsRevised' => 'i'), "Lua");
      $counter3->count("TextId")->field("IsRevised")->eq(1);
      $object3 = $assembler->find($counter3)->first();
      $revised = \doubleval($object3->get('COUNT(TextId)'));
      
      return array($translated * 100.0 / $size, $revised * 100.0 / $size);
   }

   /**
    * Calculates percentages of a given metatable.
    * @param int $tableid A metatable ID.
    * @return int A number.
    */
   private function calculateMetaTablePercentages($tableid)
   {
      $metaTable = $this->readTables($tableid);

      $factory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);

      // Count rows.
      $counter1 = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "Lang");
      $counter1->count("TableId");
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $counter1->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $counter1->ior();
         }
      }
      $object1 = $assembler->find($counter1)->first();
      $size = \doubleval($object1->get('COUNT(TableId)'));
      
      // Count translated records.
      $counter2 = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'IsTranslated' => 'i'), "Lang");
      $counter2->count("TableId");
      $counter2->field("IsTranslated")->eq(1)->iand();
      $counter2->lparen();
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $counter2->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $counter2->ior();
         }
      }
      $counter2->rparen();
      $object2 = $assembler->find($counter2)->first();
      $translated = \doubleval($object2->get('COUNT(TableId)'));      
    
      // Count revised records.
      $counter3 = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'IsRevised' => 'i'), "Lang");
      $counter3->count("TableId");
      $counter3->field("IsRevised")->eq(1)->iand();
      $counter3->lparen();
      for ($i = 0; $i < \count($metaTable); $i++)
      {
         $counter3->field("TableId")->eq($metaTable[$i + 1]);
         if ($i < \count($metaTable) - 1)
         {
            $counter3->ior();
         }
      }
      $counter3->rparen();
      $object3 = $assembler->find($counter3)->first();
      $revised = \doubleval($object3->get('COUNT(TableId)'));  
      
      return array($translated * 100.0 / $size, $revised * 100.0 / $size);
   }

   /**
    * Calculates percentages of a given table.
    * @param int $tableid A lang table ID.
    * @return int A number.
    */
   private function calculateLangPercentages($tableid)
   {
      $result = array(0.0, 0.0);
   
      $factory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      
      $counter1 = new \rocinante\mapper\identity\Identity(array('TableId' => 'i'), "Lang");
      $counter1->count("TableId")->field("TableId")->eq($tableid);
      $object1 = $assembler->find($counter1)->first();
      $size = \doubleval($object1->get('COUNT(TableId)'));
      
      if ($size > 0)
      {
         $counter2 = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'IsTranslated' => 'i'), "Lang");
         $counter2->count("TableId")->field("TableId")->eq($tableid)->iand()->field("IsTranslated")->eq(1);
         $object2 = $assembler->find($counter2)->first();
         $translated = \doubleval($object2->get('COUNT(TableId)'));

         $counter3 = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'IsRevised' => 'i'), "Lang");
         $counter3->count("TableId")->field("TableId")->eq($tableid)->iand()->field("IsRevised")->eq(1);
         $object3 = $assembler->find($counter3)->first();
         $revised = \doubleval($object3->get('COUNT(TableId)'));

         $result = array($translated * 100.0 / $size, $revised * 100.0 / $size);
      }
      
      return $result;
   }

}
