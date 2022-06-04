<?php

namespace rocinante\command\login;

require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * CheckMaintenance checks whether a user can access Rocinante. A user won't be able to access when
 * Rocinante is being maintenance and he/she is not an admin.
 */
trait CheckMaintenance
{

   /**
    * Checks whether a user can access Rocinante.
    * @return true if user can't access, false otherwise.
    */
   public function checkMaintenance()
   {
      $isMaintenanceActive = false;
      $factory = new \rocinante\persistence\PersistenceFactory("Maintenance");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $identity = $factory->getIdentity();
      $identity->field("MaintenanceId")->eq(0);
      $collection = $assembler->find($identity);
      $object = $collection->first();
      if ($object !== null)
      {
         $isMaintenanceActive = $object->get('Active') === 1;
      }

      $registry = \rocinante\command\SessionRegistry::instance();
      $registry->resume();
      return $registry->getType() !== "ADMIN" && $isMaintenanceActive;
   }

}
