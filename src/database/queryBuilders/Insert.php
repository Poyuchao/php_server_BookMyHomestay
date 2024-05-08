<?php
require_once ROOT . 'structures/Logger.php';

class InsertQueryBuilder
{
  /**
   * The instance of the QueryBuilder class that created this instance.
   */
  private QueryBuilder $queryBuilder;
  /**
   * The table to insert into.
   */
  private string $table;
  /**
   * The columns to insert into.
   */
  private array $columns = [];
  /**
   * The values to insert.
   */
  private array $values = [];

  /**
   * Columns to return.
   */
  private array $returning = [];

  private Logger $logger;

  function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;

    $this->logger = Logger::children('QueryBuilder->InsertQueryBuilder');
  }

  /**
   * Set the table to insert into.
   */
  function into($table)
  {
    $this->table = $table;
    return $this;
  }

  /**
   * Set the data to insert.
   */
  function values(array $data): InsertQueryBuilder
  {
    // Sanitize the column names and values and store them in the columns and values arrays.
    foreach ($data as $column => $value) {
      $this->columns[] = $this->queryBuilder->sanitizeName($column);
      $this->values[] = $value;
    }

    return $this;
  }

  /**
   * Set the columns to return.
   */
  function returning(array $columns): InsertQueryBuilder
  {
    $this->returning = array_map(fn ($column) => $this->queryBuilder->sanitizeName($column), $columns);
    return $this;
  }

  /**
   * Build the query.
   */
  function buildQuery(): mysqli_stmt
  {
    // Convert the columns and values arrays into strings to use in the query, e.g. (column1, column2, column3) and (?, ?, ?).
    $columns = implode(', ', $this->columns);
    // Create a string of question marks to use as placeholders for the values, e.g. ?, ?, ?.
    $values = implode(', ', array_fill(0, count($this->values), '?'));

    // Create the query string, e.g. INSERT INTO table (column1, column2, column3) VALUES (?, ?, ?).
    $query = "INSERT INTO $this->table ($columns) VALUES ($values)";

    $this->logger->debug($query);

    // Prepare the query
    $statement = $this->queryBuilder->connection->prepare($query);

    // Throw an exception if the query is invalid.
    if (!$statement) {
      throw new Exception($this->queryBuilder->connection->error);
    }

    // Create a string of types to use in the bind_param function, e.g. 'sss'.
    $types = '';
    foreach ($this->values as $value) {
      $types .= $this->queryBuilder->getBindValueType($value);
    }

    // Bind the values to the statement to prevent SQL injection.
    $statement->bind_param($types, ...$this->values);

    $this->logger->debug($this->values);


    return $statement;
  }

  function getReturnRow()
  {
    if (empty($this->returning)) {
      return null;
    }

    $columns = implode(', ', $this->returning);

    $query = "SELECT $columns FROM $this->table WHERE id = LAST_INSERT_ID()";

    $this->logger->debug($query);

    $result = $this->queryBuilder->connection->query($query);

    $results = $result->fetch_all(MYSQLI_ASSOC);

    return count($results) > 0 ? $results[0] : null;
  }

  function execute()
  {
    // Execute the query and close the statement.
    $statement = $this->buildQuery();
    $statement->execute();
    $statement->close();

    return $this->getReturnRow();
  }
}
