<?php

namespace rocinante\command;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * SelectMaintenanceMode returns a JSON string with the status of the maintenance mode, and a 
 * warning to be shown to Rocinante users.
 */
class SelectMaintenanceMode extends \rocinante\controller\Command
{

   /**
    * Extracts maintenance mode data from the database.
    * @return string A JSON string.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "SelectMaintenanceMode")
      {
         $data = null;
         $factory = new \rocinante\persistence\PersistenceFactory("Maintenance");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $identity = $factory->getIdentity();
         $identity->field("MaintenanceId")->eq(0);
         $collection = $assembler->find($identity);
         $object = $collection->first();
         if ($object !== null)
         {
            $data = array("status" => ($object->get('Active') === 0 ? "OFF" : "ON"),
                          "message" => $object->get('Message'));
         }

         if ($data === null)
         {
            throw new Exception("No data for maintenance mode");
         }

         echo \json_encode($data);
      }
   }

}
