<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * Status creates a string that shows how many string are translated, how many strings must be
 * translated, and their ratio.
 */
class Status extends \rocinante\controller\Command
{

   /**
    * Creates a string that shows how many string are translated, how many strings must be 
    * translated, and their relation (percentage).
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/Status")
      {
         $l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $factory = new \rocinante\persistence\PersistenceFactory("Status");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $statusIdentity = $factory->getIdentity();
         $statusIdentity->limit(1);
         $collection = $assembler->find($statusIdentity);
         $object = $collection->first();
         if ($object !== null )
         {
            $percentage = \number_format($object->get('Percentage'), 2, $l10n->format->{"decimal-mark"}, $l10n->format->{"thousands-mark"});
            $total = \number_format($object->get('Total'), 0, $l10n->format->{"decimal-mark"}, $l10n->format->{"thousands-mark"});
            $translated = \number_format($object->get('Translated'), 0, $l10n->format->{"decimal-mark"}, $l10n->format->{"thousands-mark"});
            $string = \sprintf($l10n->frontpage->{"translation-status"}, $translated, $total, $percentage);
            echo $string;
         }
      }
   }

}
