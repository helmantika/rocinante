<?php

namespace rocinante\command\translation;

require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateStats adds to or subtracts from user statistics a number of translated, revised, or updated 
 * strings.
 */
class UpdateStats
{
   /**
    * Adds to or substracts from user statistics a number of translated, revised, or updated strings.
    * @param int $translated Number of translated strings. It can be 0 or a negative number.
    * @param int $revised Number of revised strings. It can be 0 or a negative number.
    * @param int $updated Number of updated strings. It can be 0 or a negative number.
    */
   public function execute($translated, $revised, $updated)
   {
      // Get current user.
      $session = \rocinante\command\SessionRegistry::instance();
      $session->resume();
      $userid = $session->getUserId();
      
      $factory = new \rocinante\persistence\PersistenceFactory("Stats");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $statsIdentity = $factory->getIdentity();
      $statsIdentity->field("UserId")->eq($userid);
      $collection = $assembler->find($statsIdentity);
      $table = $collection->first();
      if ($table !== null)
      {
         $table->set('Translated', $table->get('Translated') + $translated);
         $table->set('Revised', $table->get('Revised') + $revised);
         $table->set('Updated', $table->get('Updated') + $updated);
         $table->set('Last', \date('Y-m-d'));
         $assembler->update($table);
      }
   }
}
