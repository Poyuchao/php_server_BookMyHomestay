<?php

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

  function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;
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

    if (QUERY_BUILDER_SEE_DEBUG) print_r($query . "\n");

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

    if (QUERY_BUILDER_SEE_DEBUG) {
      print_r($this->values);
      echo "\n";
    }

    return $statement;
  }

  function execute()
  {
    // Execute the query and close the statement.
    $statement = $this->buildQuery();
    $statement->execute();
    $statement->close();
  }
}
