<?php

namespace rocinante\command;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateMaintenanceMode sets maintenance mode status and/or a warning for the users.
 */
class UpdateMaintenanceMode extends \rocinante\controller\Command
{

   /**
    * Sets maintenance mode status and/or a warning for the users.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "UpdateMaintenanceMode")
      {
         $factory = new \rocinante\persistence\PersistenceFactory("Maintenance");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $identity = $factory->getIdentity();
         $identity->field("MaintenanceId")->eq(0);
         $collection = $assembler->find($identity);
         $object = $collection->first();
         if ($object !== null)
         {
            $previous = \intval($object->get('Active'));
            $object->set('Active', $this->request->getProperty('status') === "OFF" ? 0 : 1);
            $object->set('Message', $this->request->getProperty('message'));
            $assembler->update($object);
            
            // If maintenance mode is active, remove session ID from every user except administrators.
            if ($previous === 0 && $object->get('Active') === 1)
            {
               
            }
         
            // Return request data.
            $data = array("status" => $this->request->getProperty('status'),
                          "message" => $this->request->getProperty('message'));
            echo \json_encode($data);
         
         }
      }
   }

}
