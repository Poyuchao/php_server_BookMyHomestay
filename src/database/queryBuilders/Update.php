<?php
require_once ROOT . 'database/constants.php';
require_once ROOT . 'structures/Logger.php';

class UpdateQueryBuilder
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
   * The where clauses.
   */
  private array $wheres = [];
  /**
   * Returning columns.
   */
  private array $returning = [];

  private Logger $logger;

  /**
   * Explicitly allow dangerous queries and update without where.
   * This is a security risk and should be used with caution.
   */
  private bool $allowDangerousQueries = false;

  public function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;

    $this->logger = Logger::children('QueryBuilder->UpdateQueryBuilder');
  }

  /**
   * Set the table to update.
   */
  public function table($table)
  {
    $this->table = $table;
    return $this;
  }

  /**
   * Set the data to update.
   */
  public function set(array $data): UpdateQueryBuilder
  {
    // Sanitize the column names and values and store them in the columns and values arrays.
    foreach ($data as $column => $value) {
      $this->columns[] = $this->queryBuilder->sanitizeName($column);
      $this->values[] = $value;
    }

    return $this;
  }

  /**
   * Set the where clause.
   */
  public function where(string $column, $operator, $value,): UpdateQueryBuilder
  {
    $this->_verifyOperator($operator);

    $this->wheres[] = [
      'column' => $this->queryBuilder->sanitizeName($column),
      'value' => $value,
      'operator' => $operator
    ];

    return $this;
  }

  /**
   * Set the columns to return.
   */
  public function returning(array $columns): UpdateQueryBuilder
  {
    $this->returning = array_map(fn ($column) => $this->queryBuilder->sanitizeName($column), $columns);
    return $this;
  }

  public function allowDangerousQueries(): UpdateQueryBuilder
  {
    $this->allowDangerousQueries = true;
    return $this;
  }

  /**
   * Build the query.
   */
  public function buildQuery(): mysqli_stmt
  {
    // Convert the columns and values arrays into strings to use in the query, e.g. column1 = ?, column2 = ?, column3 = ?.
    $set = implode(', ', array_map(fn ($column) => "$column = ?", $this->columns));

    // Create the query string, e.g. UPDATE table SET column1 = ?, column2 = ?, column3 = ?.
    $query = "UPDATE $this->table SET $set";

    if (!$this->allowDangerousQueries && empty($this->wheres)) {
      throw new Exception('Dangerous query, update without where. Use allowDangerousQueries() to allow dangerous queries.');
    }

    // Add the where clauses to the query.
    if (count($this->wheres) > 0) {
      $wheres = implode(' AND ', array_map(fn ($where) => "$where[column] $where[operator] ?", $this->wheres));
      $query .= " WHERE $wheres";
    }

    $this->logger->debug($query);

    // Prepare the query and return the statement.
    $stmt = $this->queryBuilder->connection->prepare($query);
    if (!$stmt) {
      throw new Exception($this->queryBuilder->connection->error);
    }

    // Bind the values to the statement.
    $types = '';
    // Map the where values to an array.
    $whereValues = array_map(fn ($where) => $where['value'], $this->wheres);
    // Merge the values and where values. The first array should be teh update values and the second should be where values.
    $bindValues = array_merge($this->values, $whereValues);
    $values = [];
    // Get the bind value type for each value.
    foreach ($bindValues as $value) {
      $types .= $this->queryBuilder->getBindValueType($value);
      $values[] = $value;
    }
    // Bind the values to the statement.
    $stmt->bind_param($types, ...$bindValues);

    $this->logger->debug($types);
    $this->logger->debug($bindValues);


    return $stmt;
  }

  public function getReturnRows()
  {
    if (empty($this->returning)) {
      return NULL;
    }

    $columns = implode(', ', $this->returning);
    $query = "SELECT $columns FROM $this->table WHERE ";
    $wheres = [];
    foreach ($this->wheres as $where) {
      $wheres[] = "$where[column] $where[operator] ?";
    }
    $query .= implode(' AND ', $wheres);

    $this->logger->debug($query);

    $stmt = $this->queryBuilder->connection->prepare($query);
    if (!$stmt) {
      throw new Exception($this->queryBuilder->connection->error);
    }

    $types = '';
    $values = [];
    foreach ($this->wheres as $where) {
      $types .= $this->queryBuilder->getBindValueType($where['value']);
      $values[] = $where['value'];
    }

    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Execute the query.
   */
  public function execute()
  {
    $stmt = $this->buildQuery();
    $stmt->execute();
    return $this->getReturnRows() ?? $stmt->affected_rows;
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
