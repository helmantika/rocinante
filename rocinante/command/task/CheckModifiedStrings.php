<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * CheckModifiedStrings reports whether translators can create updating tasks.
 */
class CheckModifiedStrings extends \rocinante\controller\Command
{
      
   /**
    * Reports whether translators can create updating tasks.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/CheckModifiedStrings")
      {
         $result =  $this->countStrings("Lang") === 0 && $this->countStrings("Lua") === 0;
         $array = array('result' => !$result);
         echo \json_encode($array);
      }
   }
   
   /**
    * Returns how many strings of a table are modified and not fixed.
    * @param $table "Lang", or "Lua"
    * @return int The number of modified strings.
    */
   private function countStrings($table)
   {
      $factory = new \rocinante\persistence\PersistenceFactory($table);
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $identity->count("IsModified")->field("IsModified")->eq(1);
      $result = $assembler->find($identity)->first();
      return \intval($result->get('COUNT(IsModified)'));
   }
}

