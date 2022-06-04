<?php

namespace rocinante\command\translation;

require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateStatus updates the number of string translated.
 */
class UpdateStatus
{

   /**
    * Updates the number of string translated.
    * @param int $number A number to add or subtract (it can be negative).
    */
   public function execute($number)
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Status");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $identity->field("StatusId")->eq(0);
      $collection = $assembler->find($identity);
      $row = $collection->first();
      if ($row !== null)
      {
         $translated = \floatval($row->get('Translated'));
         $total = \floatval($row->get('Total'));
         $translated += $number;
         $percentage = \floatval($translated * 100.0 / $total);
         $row->set('Translated', $translated);
         $row->set('Percentage', $percentage);
         $assembler->update($row);
      }
   }

}
