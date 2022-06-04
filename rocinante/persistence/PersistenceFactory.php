<?php

namespace rocinante\persistence;

require_once('rocinante/mapper/SelectionFactory.php');
require_once('rocinante/mapper/DeletionFactory.php');
require_once('rocinante/domain/collection/JumbleCollection.php');
require_once('rocinante/domain/factory/JumbleFactory.php');
      
/**
 * PersistenceFactory creates factories that are bound to a given domain object.
 */
class PersistenceFactory
{

   /**
    * Creates a persistence factory for the given object.
    * @param \rocinante\domain\Domain $object A domain object name.
    */
   public function __construct($object)
   {
      $this->object = $object;
   }

   /**
    * Returns a domain object factory.
    */
   public function getDomainFactory()
   {
      $commandClass = $this->getFactory("domain/factory", "DomainFactory", "Factory");
      return $commandClass->newInstance();
   }

   /**
    * Returns a collection of domain objects.
    * @param array $array Raw data where keys are field names and values are field values.
    */
   public function getCollection($array)
   {
      $commandClass = $this->getFactory("domain/collection", "Collection", "Collection");
      return $commandClass->newInstance($array, $this->getDomainFactory());
   }

   /**
    * Returns a collection of jumble objects.
    * @param type $array Raw data where keys are field names and values are field values.
    */
   public function getJumbleCollection($array)
   {
      return new \rocinante\domain\collection\JumbleCollection($array, new \rocinante\domain\factory\JumbleFactory());
   }
   
   /**
    * Returns a selection factory to perform queries.
    */
   public function getSelectionFactory()
   {
      return new \rocinante\mapper\SelectionFactory();
   }

   /**
    * Returns an update factory to add new domain objects or update old ones.
    */
   public function getUpdateFactory()
   {
      $commandClass = $this->getFactory("mapper/update", "UpdateFactory", "UpdateFactory");
      return $commandClass->newInstance();
   }
   
   /**
    * Returns a deletion factory to remove domain objects from the database.
    */
   public function getDeletionFactory()
   {
      return new \rocinante\mapper\DeletionFactory();
   }

   /**
    * Gets a canonical identity object (all fields are set). It's used to define query constraints.
    * @return \rocinante\mapper\Identity A canonical identity object.
    */
   public function getIdentity()
   {
      $commandClass = $this->getFactory("mapper/identity", "Identity", "Identity");
      return $commandClass->newInstance();
   }
   
   /**
    * Creates a new factory.
    * @param type $package The directory where the file is.
    * @param type $factory The base of the factory.
    * @param type $suffix The suffix used to identify this kind of factories.
    * @return \ReflectionClass A reflection class of the suitable factory.
    * @throws \Exception The factory can't be created.
    */
   private function getFactory($package, $factory, $suffix)
   {
      $result = null;
      $filepath = "rocinante/$package/{$this->object}$suffix.php";
      $namespace = \str_replace("/", "\\", $package);
      $classname = "\\rocinante\\$namespace\\{$this->object}$suffix";

      if (\file_exists($filepath))
      {
         require_once($filepath);
         if (\class_exists($classname))
         {
            $commandClass = new \ReflectionClass($classname);
            if ($commandClass->isSubclassOf("\\rocinante\\$namespace\\$factory"))
            {
               $result = $commandClass;
            }
         }
      }
      
      if ($result === null)
      {
         throw new Exception("\\rocinante\\$namespace\\{$this->object}$suffix can't be created");
      }

      return $result;
   }
}
