<?php

namespace rocinante\persistence;

/**
 * SqlManager implements core functionality for making database requests.
 */
class SqlManager
{

   /**
    * The one and only instance of this class.
    * @var \rocinante\persistence\SqlManager
    */
   private static $instance = null;
   
   /**
    * The object which represents a connection to a MySQL server.
    * @var \mysqli
    */
   protected $mysqli = null;

   /**
    * The SQL statements used by a data manager.
    * @var array
    */
   protected static $statements = array();

   /**
    * Opens a connection to a database.
    */
   private function __construct()
   {
      if (!\file_exists("config/connect.xml"))
      {
         throw new \Exception("Connect file was not found");
      }

      $connect = \simplexml_load_file("config/connect.xml");
      
      $this->mysqli = new \mysqli($connect->host, $connect->user, $connect->password, $connect->database);
      if ($this->mysqli)
      {
         $this->mysqli->set_charset('utf8');
      } 
      elseif (\mysqli_connect_error($this->mysqli))
      {
         throw new \Exception("Unable to connect to a MySQL server. Connect error: " . \mysqli_connect_error() );
      }
   }

   /**
    * Closes the database connection.
    */
   public function __destruct()
   {
      $statements = \array_keys(self::$statements);
      foreach ($statements as $stmt)
      {
         $this->close($stmt);
      }
      $this->mysqli->close();
   }

   /**
    * Returnst the one and only instance of this class.
    * @return SqlManager The SqlManager instance.
    */
   public static function instance()
   {
      if (is_null(self::$instance))
      {
         self::$instance = new self();
      }
      return self::$instance;
   }
   
   /**
    * Prepares an SQL statement.
    * @param string $statement The query as a string. Don't add a semicolon at the end.
    * @returns mysqli_stmt A statement object.
    */
   private function prepare($statement)
   {
      $mysqli_stmt = null;

      // If a SQL statement is already prepared, return its handler.
      if (isset(self::$statements[$statement]))
      {
         $mysqli_stmt = self::$statements[$statement];
      }
      // Prepare the statement now.
      else
      {
         $mysqli_stmt = $this->mysqli->prepare($statement);
         if ($mysqli_stmt !== false)
         {
            self::$statements[$statement] = $mysqli_stmt;
         } 
         else
         {
            throw new \Exception("Invalid SQL statement: $statement");
         }
      }
      return $mysqli_stmt;
   }

   /**
    * Executes a prepared SQL statement.
    * @param string $statement The query as a string. Don't add a semicolon at the end.
    * @param string $types A string that contains one or more characters which specify the types for
    * the corresponding bind variables: i (integer), d (double), s (string), and b (blob).
    * @param array $values Params to bind to the statement.
    * @return bool true on success or false on failure.
    */
   public function execute($statement, $types, array $values)
   {
      // Prepare the statement.
      $mysqli_stmt = $this->prepare($statement);

      // Bind values regardless of the number of them.
      $ref = new \ReflectionClass("mysqli_stmt");
      $method = $ref->getMethod("bind_param");
      // First param of mysqli_stmt::bind_param method is the type string.
      \array_unshift($values, $types);
      $method->invokeArgs($mysqli_stmt, $values);

      // Execute the statement.
      $result = $mysqli_stmt->execute();
      return $result;
   }

   /**
    * Executes a SQL statement.
    * @param string $statement The query given in a string. Don't add a semicolon at the end.
    */
   public function query($statement)
   {
      $mysqli_result = $this->mysqli->query($statement);
      if ($mysqli_result !== false)
      {
         self::$statements[$statement] = $mysqli_result;
      }
      return $mysqli_result;
   }
   
   /**
    * Executes several SQL statements. Their results won't be stored anywhere.
    * @param string $statements The queries given in a string. Each one must be end with a semicolon.
    * @return bool false if the first statement failed.
    */
   public function multiQuery($statements)
   {
      $result1 = $this->mysqli->multi_query($statements);
      while ($this->mysqli->more_results())
      {
         $this->mysqli->next_result();
      }
      return $result1;
   }
   
   /**
    * Transfers a result set from a prepared statement.
    * @param type $statement The query as a string. Don't add a semicolon at the end.
    * @return true on success or false on failure. 
    */
   public function storeResult($statement)
   {
      $mysqli_stmt = self::$statements[$statement];
      return $mysqli_stmt->store_result();
   }
   
   /**
    * Binds variables to a prepared statement for result storage.
    * @param string $statement The query as a string. Don't add a semicolon at the end.
    * @param array $variables Column names to bind.
    * @param bool $qualifiedFields If it's true then field names will be qualified (table name and 
    * a dot will be prepend).
    * @return bool true on success or false on failure.
    */
   public function bind($statement, array &$variables, $qualifiedFields = false)
   {
      $mysqli_stmt = self::$statements[$statement];

      // Bind variables regardless of the number of them.
      $meta = $mysqli_stmt->result_metadata();
      while ($field = $meta->fetch_field())
      {
         $fieldname = $qualifiedFields ? $field->table . "." . $field->name : $field->name;
         $params[] = &$variables[$fieldname];
      }

      $result = \call_user_func_array(array($mysqli_stmt, "bind_result"), $params);
      return $result !== false;
   }

   /**
    * Fetches results from a prepared statement into the bound variables.
    * @param string $statement The query as a string. Don't add a semicolon at the end.
    * @return mixed An associative array that binds column names and values of the current row,
    * or false when there's no more rows to read.
    */
   public function fetch($statement, array &$variables)
   {
      $result = false;
      $mysqli_stmt = self::$statements[$statement];
      if ($mysqli_stmt->fetch())
      {
         foreach ($variables as $key => $value)
         {
            if (\is_string($key))
            {
               $c[$key] = $value;
            }
         }
         $result = $c;
      }

      return $result;
   }

   /**
    * Fetches results from a statement.
    * @param string $statement The query as a string. Don't add a semicolon at the end.
    * @return mixed An associative array where keys are column names and values are field values, 
    * or false when there's no more rows to read.
    */
   public function fetchAssoc($statement)
   {
      $mysqli_result = self::$statements[$statement];
      return $mysqli_result->fetch_assoc();
   }
   
   /**
    * Frees stored result memory for a given statement handle.
    * @param type $statement The query as a string. Don't add a semicolon at the end.
    */
   public function freeResult($statement)
   {
      $mysqli_stmt = self::$statements[$statement];
      return $mysqli_stmt->free_result();
   }
   
   /**
    * Resets a prepared statement.
    * @param type $statement The query as a string. Don't add a semicolon at the end.
    */
   public function reset($statement)
   {
      $mysqli_stmt = self::$statements[$statement];
      $mysqli_stmt->reset();
   }

   /**
    * Closes a prepared or non-prepared statement.
    * @param type $statement The query as a string. Don't add a semicolon at the end.
    */
   public function close($statement)
   {
      $mysqli_stmt = self::$statements[$statement];
      if (!\is_bool($mysqli_stmt))
      {
         $mysqli_stmt->close();
      }
      unset(self::$statements[$statement]);
   }

   /**
    * Checks whether an INSERT/UPDATE/DELETE statement was executed successfully.
    * @param type $statement The query as a string. Don't add a semicolon at the end.
    * @return int Number of affected rows or -1 on failure.
    */
   public function isUpdated($statement)
   {
      $mysqli_stmt = self::$statements[$statement];
      return $mysqli_stmt->affected_rows;
   }

   /**
    * Returns the ID generated by the query on a table with a column having the AUTO_INCREMENT
    * attribute, or zero if the modified table does not have a column with the AUTO_INCREMENT
    * attribute.
    * @param string $statement The query as a string. Don't add a semicolon at the end.
    * @return int -1 on failure, or a number equal or greater than 0 on success.
    */
   public function lastInsertId($statement)
   {
      $mysqli_stmt = self::$statements[$statement];
      return $mysqli_stmt->insert_id;
   }

   /**
    * Escapes special characters in a string for use in an SQL statement, taking into account the 
    * current charset of the connection.
    * @param string $string A string.
    * @return string An escaped string.
    */
   public function escape($string)
   {
      return $this->mysqli->real_escape_string($string);
   }
}
