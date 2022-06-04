<?php

namespace rocinante\persistence;

require_once 'rocinante/domain/model/Domain.php';
require_once 'rocinante/domain/collection/Collection.php';
require_once 'rocinante/persistence/PersistenceFactory.php';
require_once 'rocinante/persistence/SqlManager.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/mapper/SelectionFactory.php';

/**
 * DomainAssembler is the data layer controller.
 */
class DomainAssembler
{

   /**
    * The core functionality for making database requests.
    * @var \rocinante\persistence\SqlManager 
    */
   protected static $sqlm;

   /**
    * The persistence factory.
    * @var \rocinante\persistence\PersistenceFactory
    */
   protected $factory;

   /**
    * Creates a new domain assembler based on a persistence factory.
    * @param \rocinante\persistence\PersistenceFactory $factory A persistence factory.
    */
   public function __construct(\rocinante\persistence\PersistenceFactory $factory)
   {
      $this->factory = $factory;
      if (!isset(self::$sqlm))
      {
         self::$sqlm = \rocinante\persistence\SqlManager::instance();
      }
   }

   /**
    * Retrieves data by means of an identity object that encapsulates a database query.
    * @param \rocinante\mapper\identity\Identity $identity An identity object.
    * @return \rocinante\domain\collection\Collection A collection of domain objects.
    */
   public function find(\rocinante\mapper\identity\Identity $identity)
   {
      // Build a query.
      $selectionFactory = $this->factory->getSelectionFactory();
      list($fields, $selection, $types, $values) = $selectionFactory->select($identity);

      // Execute the query.
      $hasJoins = $identity->hasJoins();
      $raw = \count($values) > 0 ? $this->preparedSelectStmt($fields, $selection, $types, $values, $hasJoins) : $this->selectStmt($selection, $hasJoins);
      return $hasJoins || $identity->isCounter() ? $this->factory->getJumbleCollection($raw) : $this->factory->getCollection($raw);
   }

   /**
    * Inserts a new domain object into the database.
    * @param \rocinante\domain\model\Domain $object A domain object.
    * @return bool true if everything was right, otherwise false.
    */
   public function insert(\rocinante\domain\model\Domain $object)
   {
      $result = false;
      
      $updateFactory = $this->factory->getUpdateFactory();
      list( $update, $types, $values ) = $updateFactory->insert($object);

      // SqlManager::execute works with references only.
      foreach ($values as &$value)
      {
         $refValues[] = &$value;
      }
      // Execute the query.
      if (self::$sqlm->execute($update, $types, $refValues))
      {
         // If the domain object was a new one, set its ID.
         $object->setLastInsertId(self::$sqlm->lastInsertId($update));
         $result = true;
      }

      // Reset the statement.
      self::$sqlm->reset($update);
      
      return $result;
   }

   /**
    * Updates the database with a domain object.
    * @param \rocinante\domain\model\Domain $object A domain object.
    */
   public function update(\rocinante\domain\model\Domain $object)
   {      
      $updateFactory = $this->factory->getUpdateFactory();
      list( $update, $types, $values ) = $updateFactory->update($object);

      // SqlManager::execute works with references only.
      foreach ($values as &$value)
      {
         $refValues[] = &$value;
      }
      // Execute the query.
      $result = (bool) self::$sqlm->execute($update, $types, $refValues);

      // Reset the statement.
      self::$sqlm->reset($update);
      
      return $result;
   }
   
   /**
    * Deletes data by means of an identity object that encapsulates a database query.
    * @param \rocinante\mapper\identity\Identity $identity An identity object.
    * @param array $tables The name of the tables whose rows will be deleted. If null then table 
    * of the identity object will be used.
    */
   public function delete(\rocinante\mapper\identity\Identity $identity, $tables = null)
   {
      // Build a query.
      $deletionFactory = $this->factory->getDeletionFactory();
      list($deletion, $types, $values) = $deletionFactory->delete($identity, $tables);

      // Execute the query.
      if (\count($values) > 0)
      {
         $this->preparedDeleteStmt($deletion, $types, $values);
      }
      else
      {
         $this->deleteStmt($deletion);
      }
   }
   
   /**
    * Executes a prepared SELECT statement.
    * @param array $fields Selected field names.
    * @param string $selection A query.
    * @param string $types A string that contains one or more characters which specify the types for
    * the corresponding bind variables: i (integer), d (double), s (string), and b (blob).
    * @param array $values Values for placeholders.
    * @param bool $hasJoins The query is SELECT ... JOIN statement.
    * @return array Raw data where keys are field names and values are field values.
    */
   private function preparedSelectStmt($fields, $selection, $types, $values, $hasJoins)
   {
      // SqlManager::execute works with references only.
      foreach ($values as &$value)
      {
         $refValues[] = &$value;
      }
      self::$sqlm->execute($selection, $types, $refValues);
      self::$sqlm->storeResult($selection);
      
      // Bind selected fields.
      // SqlManager::bind works with references only.
      foreach ($fields as &$field)
      {
         $refFields[] = &$field;
      }
      self::$sqlm->bind($selection, $refFields, $hasJoins);

      // Store each row.
      $raw = null;
      while ($result = self::$sqlm->fetch($selection, $refFields))
      {
         $raw[] = $result;
      }

      self::$sqlm->freeResult($selection);      
      self::$sqlm->reset($selection);
      
      return $raw;
   }
   
   /**
    * Executes a SELECT statement.
    * @param string $selection A query.
    * @param bool $hasJoins The query is SELECT ... JOIN statement. Fields will be aliased.
    * @return array Raw data where keys are field names and values are field values.
    */
   private function selectStmt($selection, $hasJoins)
   {
      // Make an allias for every field.
      if ($hasJoins && \strpos($selection, "COUNT") === false)
      {
         $pos = \strpos($selection, " FROM");
         if ($pos !== false)
         {
            $start = 7 + (\strpos($selection, "DISTINCT") !== false ? 9 : 0); // 7 because of "SELECT " length.
            $nonAliasedFields = \substr($selection, $start, $pos - $start);
            $fields = \explode( ",", $nonAliasedFields);
            foreach($fields as &$field)
            {
               $field .= " AS `$field`";
            }
            $aliasedFields = \implode(",", $fields);
            $selection = \str_replace($nonAliasedFields, $aliasedFields, $selection);
         }
      }
      
      self::$sqlm->query($selection);
      while($row = self::$sqlm->fetchAssoc($selection))
      {
         $raw[] = $row;
      }
      self::$sqlm->close($selection);
      return $raw;
   }
   
   /**
    * Executes a prepared DELETE statement.
    * @param string $deletion A query.
    * @param string $types A string that contains one or more characters which specify the types for
    * the corresponding bind variables: i (integer), d (double), s (string), and b (blob).
    * @param array $values Values for placeholders.
    * @return int Number of affected rows or -1 on failure.
    */
   private function preparedDeleteStmt($deletion, $types, $values)
   {
      $result = -1;
      
      // SqlManager::execute works with references only.
      foreach ($values as &$value)
      {
         $refValues[] = &$value;
      }
      
      // Execute the query.
      if (self::$sqlm->execute($deletion, $types, $refValues))
      {
         // If the domain object was a new one, set its ID.
         $result = self::$sqlm->isUpdated($deletion);
      }
 
      self::$sqlm->reset($deletion);
      
      return $result;
   }
   
   /**
    * Executes a DELETE statement.
    * @param string $deletion A query.
    * @return int Number of affected rows or -1 on failure.
    */
   private function deleteStmt($deletion)
   {
      self::$sqlm->query($deletion);
      $result = self::$sqlm->isUpdated($deletion);
      return $result;
   }
}
