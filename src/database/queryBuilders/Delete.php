<?php

class DeleteQueryBuilder
{
  /**
   * The instance of the QueryBuilder class that created this instance.
   */
  private QueryBuilder $queryBuilder;
  /**
   * The table to select from.
   */
  private string $table;
  /**
   * The where clauses.
   */
  private array $wheres = [];
  /**
   * Return first row only.
   */
  private bool $first = false;

  /**
   * Explicitly allow dangerous queries and delete without where.
   * This is a security risk and should be used with caution.
   */
  private bool $allowDangerousQueries = false;

  /**
   * The constructor of the DeleteQueryBuilder class.
   */
  function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;
  }

  /**
   * Set the table to select from.
   */
  function from($table)
  {
    $this->table = $table;
    return $this;
  }


  /**
   * Add a where clause to the query.
   * Allowed operators: =, !=, >, <, >=, <=, ILIKE, LIKE, NOT LIKE, IN, NOT IN
   */
  function where($column, $operator, $value): DeleteQueryBuilder
  {
    // Verify that the operator is allowed.
    $this->_verifyOperator($operator);
    // Sanitize the column name and store the where clause in the wheres array.
    $sanitizedColumn = $this->queryBuilder->sanitizeName($column);

    // Add the where clause to the wheres array.
    $this->wheres[] = [
      'column' => $sanitizedColumn,
      'operator' => $operator,
      'value' => $value
    ];
    return $this;
  }

  function allowDangerousQueries(): DeleteQueryBuilder
  {
    $this->allowDangerousQueries = true;
    return $this;
  }

  /**
   * Build the query.
   */
  function buildQuery(): mysqli_stmt
  {
    // Create the query string, e.g. DELETE FROM table.
    $query = "DELETE FROM $this->table";

    if (!$this->allowDangerousQueries && empty($this->wheres)) {
      throw new Exception('Dangerous query, delete without where. Use allowDangerousQueries() to allow dangerous queries.');
    }

    // Add the where clauses to the query if there are any.
    if (!empty($this->wheres)) {
      $query .= ' WHERE ';
      $wheres = [];
      // Add the where clauses to the query, e.g. column1 = ? AND column2 > ?.
      foreach ($this->wheres as $where) {
        $wheres[] = "`{$where['column']}` {$where['operator']} ?";
      }

      // Add the where clauses to the query, e.g. delete from table where column1 = ? AND column2 > ?.
      $query .= implode(' AND ', $wheres);
    }

    if (QUERY_BUILDER_SEE_DEBUG) print_r($query . "\n");

    // Prepare the query.
    $statement = $this->queryBuilder->connection->prepare($query);

    // Throw an exception if the query is invalid.
    if (!$statement) {
      throw new Exception($this->queryBuilder->connection->error);
    }

    if (!empty($this->wheres)) {
      $types = '';
      $values = [];
      foreach ($this->wheres as $where) {
        $types .= $this->queryBuilder->getBindValueType($where['value']);
        $values[] = $where['value'];
      }

      // Bind the values to the statement to prevent SQL injection.
      $statement->bind_param($types, ...$values);

      if (QUERY_BUILDER_SEE_DEBUG) {
        print_r($types);
        echo PHP_EOL;
        print_r($values);
        echo PHP_EOL;
      }
    }

    return $statement;
  }

  /**
   * Execute the query against the database and return the result.
   * If ->first() was called, will return the first row only or NULL if no rows were found.
   */
  function execute()
  {
    // Build the query and execute it.
    $statement = $this->buildQuery();
    $statement->execute();
    // Get the result of the query and return it.
    $result = $statement->get_result();
    $allRows = $result->fetch_all(MYSQLI_ASSOC);

    if ($this->first) {
      return count($allRows) > 0 ? $allRows[0] : NULL;
    }

    // Close the statement and return the result.
    $statement->close();
    return $allRows;
  }

  /**
   * Verify that the operator is allowed.
   */
  private function _verifyOperator($operator): void
  {
    if (!in_array($operator, ALLOWED_OPERATORS)) {
      throw new Exception('Invalid operator');
    }
  }
}
