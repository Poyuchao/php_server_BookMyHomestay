<?php
require_once 'database/index.php';
require_once 'database/queryBuilders/Select.php';
require_once 'database/queryBuilders/Insert.php';

class QueryBuilder
{
  /**
   * The connection to the database.
   */
  public mysqli $connection;
  /**
   * The instance of the SelectQueryBuilder or InsertQueryBuilder class.
   */
  private SelectQueryBuilder|InsertQueryBuilder $queryBuilder;

  function __construct($connection)
  {
    $this->connection = $connection;
  }

  /**
   * Select columns from the table.
   * This function should not recieve user input directly, as it is vulnerable to SQL injection.
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
   * Sanitize the name of a column or table.
   */
  function sanitizeName($name): string
  {
    return $this->connection->real_escape_string($name);
  }

  function execute()
  {
    if (!isset($this->queryBuilder)) {
      throw new Exception('No query type specified');
    }

    return $this->queryBuilder->execute();
  }
}
