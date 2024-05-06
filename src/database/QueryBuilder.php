<?php
require_once ROOT . 'database/queryBuilders/Select.php';
require_once ROOT . 'database/queryBuilders/Insert.php';
require_once ROOT . 'database/queryBuilders/Update.php';
require_once ROOT . 'database/queryBuilders/Delete.php';
require_once ROOT . 'database/queryBuilders/Raw.php';

class QueryBuilder
{
  /**
   * The connection to the database.
   */
  public mysqli $connection;
  /**
   * The instance of the SelectQueryBuilder or InsertQueryBuilder class.
   */
  private SelectQueryBuilder|InsertQueryBuilder|UpdateQueryBuilder|DeleteQueryBuilder|RawQueryBuilder $queryBuilder;

  /**
   * Raw query string.
   */
  public string $_rawQuery;

  function __construct(mysqli $connection)
  {
    $this->connection = $connection;
  }

  /**
   * Select columns from the table.
   * This function should not receive user input directly, as it is vulnerable to SQL injection.
   */
  function select(?array $columns = ['*']): SelectQueryBuilder
  {
    if (isset($columns) && !is_array($columns)) {
      $columns = [$columns];
    }

    $this->queryBuilder = new SelectQueryBuilder($this, $columns ?? ['*']);
    return $this->queryBuilder;
  }

  /**
   * Insert data into the table.
   */
  function insert(): InsertQueryBuilder
  {
    $this->queryBuilder = new InsertQueryBuilder($this);
    return $this->queryBuilder;
  }

  /**
   * Update data in the table.
   */
  function update(): UpdateQueryBuilder
  {
    $this->queryBuilder = new UpdateQueryBuilder($this);
    return $this->queryBuilder;
  }

  /**
   * Delete data from the table.
   */
  function delete(): DeleteQueryBuilder
  {
    $this->queryBuilder = new DeleteQueryBuilder($this);
    return $this->queryBuilder;
  }

  /**
   * Set the raw query string.
   * This function should not receive user input directly, as it is vulnerable to SQL injection.
   */
  function raw(string $query): RawQueryBuilder
  {
    $this->queryBuilder = new RawQueryBuilder($this, $query);
    return $this->queryBuilder;
  }

  /**
   * Sanitize the name of a column or table.
   */
  function sanitizeName(string $name): string
  {
    if ($name === '*') {
      return $name;
    }

    $explodedColumnNames = explode('.', $name);
    $sanitizedColumnNames = array_map(fn ($column) => "`" . $this->connection->real_escape_string($column) . "`", $explodedColumnNames);

    return implode('.', $sanitizedColumnNames);
  }

  /**
   * Convert value to the bind parameter type.
   */
  function getBindValueType(mixed $value): string
  {
    // Return the bind parameter type based on the type of the value.
    // Available types are: i - integer, d - double, s - string, b - BLOB (not implemented).
    switch (gettype($value)) {
      case 'integer':
        return 'i';
      case 'double':
      case 'float':
        return 'd';
      case 'string':
        return "s";
      default:
        return 's';
    }
  }

  function execute()
  {
    if (!isset($this->queryBuilder) && !isset($this->_rawQuery)) {
      throw new Exception('No query builder set.');
    }

    if (isset($this->_rawQuery)) {
      return $this->connection->query($this->_rawQuery);
    }

    return $this->queryBuilder->execute();
  }


  /**
   * Create a new instance of the QueryBuilder class.
   */
  public static function create(mysqli $connection)
  {
    return new QueryBuilder($connection);
  }
}
