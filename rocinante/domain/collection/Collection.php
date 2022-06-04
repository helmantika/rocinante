<?php

namespace rocinante\domain\collection;

/**
 * Collection defines an interface to create a domain object group.
 */
abstract class Collection
{

   /**
    * The data of the objects that are part of the collection.
    * @var array 
    */
   protected $raw = array();

   /**
    * The domain factory.
    * @var \rocinante\domain\DomainFactory
    */
   protected $factory = null;

   /**
    * The objects that are part of the collection.
    * @var array
    */
   protected $objects = array();

   /**
    * The collection size.
    * @var int 
    */
   protected $total = 0;

   /**
    * Creates a new collection. If both parameters are set, a collection will be created from raw
    * data. However, a collection always returns domain objects. In other words, a domain object 
    * will be created from raw data before returning it.
    * @param array $raw Raw data of the objects that are part of the collection.
    * @param \rocinante\domain\factory\DomainFactory $factory A domain factory.
    */
   public function __construct(array $raw = null, \rocinante\domain\factory\DomainFactory $factory = null)
   {
      if (!\is_null($raw) && !\is_null($factory))
      {
         $this->raw = $raw;
         $this->total = \count($raw);
         $this->factory = $factory;
      }
   }

   /**
    * Adds a new domain object to this collection.
    * @param \rocinante\domain\model\Domain $object A domain object.
    * @throws \Exception The domain class does not belong to this collection.
    */
   public function add(\rocinante\domain\model\Domain $object)
   {
      $class = $this->targetClass();
      if (!($object instanceof $class))
      {
         throw new \Exception("This is a $class collection");
      }
      $this->objects[$this->total] = $object;
      $this->total++;
   }

   /**
    * When it is called, it returns a domain object that can be iterated over.
    */
   public function getGenerator()
   {
      for ($x = 0; $x < $this->total; $x++)
      {
         yield($this->getRow($x));
      }
   }

   /**
    * Gets the collection size.
    * @return int A number.
    */
   public function size()
   {
      return $this->total;
   }

   /**
    * Gets the first domain object in the collection.
    * @return \rocinante\domain\model\Domain A domain object, or null whether the collection is empty.
    */
   public function first()
   {
      $object = null;
      if ($this->total > 0)
      {
         $object = $this->getRow(0);
      }
      return $object;
   }
   
   /**
    * Returns the domain class this collection has.
    */
   abstract protected function targetClass();

   /**
    * Gets a domain object located at a given row (index).
    * @param int $number An index number.
    * @return \rocinante\domain\model\Domain A domain object, or null whether the index is not valid.
    */
   private function getRow($number)
   {
      $object = null;
      if ($number >= 0 && $number < $this->total)
      {
         if (isset($this->objects[$number]))
         {
            $object = $this->objects[$number];
         } 
         elseif (isset($this->raw[$number]))
         {
            $this->objects[$number] = $this->factory->createObject($this->raw[$number]);
            $object = $this->objects[$number];
         }
      }
      return $object;
   }

}
