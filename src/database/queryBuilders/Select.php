<?php
require_once ROOT . 'database/constants.php';

class SelectQueryBuilder
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
   * The columns to select.
   */
  private array $columns;
  /**
   * The joins to perform.
   */
  private array $joins = [];
  /**
   * The where clauses.
   */
  private array $wheres = [];
  /**
   * The order by clauses.
   */
  private array $orders = [];
  /**
   * The limit of rows to return.
   */
  private int $limit;
  /**
   * The offset of rows to return.
   */
  private int $offset;
  /**
   * Return first row only.
   */
  private bool $first = false;

  /**
   * The constructor of the SelectQueryBuilder class.
   */
  function __construct(QueryBuilder $queryBuilder, array $columns)
  {
    $this->queryBuilder = $queryBuilder;
    // Sanitize the column names and store them in the columns array.
    $this->columns = array_map(fn ($column) => $this->queryBuilder->sanitizeName($column), $columns);
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
   * This function should not recieve user input directly, as it is vulnerable to SQL injection.
   *
   * Perform a join on the table.
   */
  function join($table, $column1, $column2, $type = 'JOIN'): SelectQueryBuilder
  {
    // sanitize the table and column names and store them in the joins array.
    $sanitizedTable = $this->queryBuilder->sanitizeName($table);
    $sanitizedColumn1 = $this->queryBuilder->sanitizeName($column1);
    $sanitizedColumn2 = $this->queryBuilder->sanitizeName($column2);
    // Add the join to the joins array.
    $this->joins[] = [
      'type' => $type,
      'table' => $sanitizedTable,
      'column1' => $sanitizedColumn1,
      'column2' => $sanitizedColumn2
    ];
    return $this;
  }

  /**
   * Add a where clause to the query.
   * Allowed operators: =, !=, >, <, >=, <=, ILIKE, LIKE, NOT LIKE, IN, NOT IN
   */
  function where($column, $operator, $value): SelectQueryBuilder
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

  /**
   * Add an order by clause to the query.
   * Allowed directions: ASC, DESC
   */
  function orderBy($column, $direction = 'ASC'): SelectQueryBuilder
  {
    // Verify that the direction is allowed.
    if (!in_array($direction, ['ASC', 'DESC'])) {
      throw new Exception('Invalid direction');
    }

    // Sanitize the column name and store the order by clause in the orders array.
    $sanitizedColumn = $this->queryBuilder->sanitizeName($column);
    // Add the order by clause to the orders array.
    $this->orders[] = [
      'column' => $sanitizedColumn,
      'direction' => $direction
    ];
    return $this;
  }

  /**
   * Limit the number of rows to return.
   */
  function limit($limit): SelectQueryBuilder
  {
    $this->limit = $limit;
    return $this;
  }

  /**
   * Offset the rows to return.
   */
  function offset($offset): SelectQueryBuilder
  {
    $this->offset = $offset;
    return $this;
  }

  /**
   * Return the first row only.
   * OBS: This will set the limit to 1.
   */
  function first(): SelectQueryBuilder
  {
    $this->limit = 1;
    $this->first = true;
    return $this;
  }

  /**
   * Build the query.
   */
  function buildQuery(): mysqli_stmt
  {
    // Convert the columns array into a string to use in the query, e.g. column1, column2, column3.
    $columns = implode(', ', $this->columns);
    // Create the query string, e.g. SELECT column1, column2, column3 FROM table.
    $query = "SELECT $columns FROM $this->table";

    // Add the joins to the query if there are any.
    foreach ($this->joins as $join) {
      // Add the join to the query, e.g. INNER JOIN table ON column1 = column2.
      $query .= " {$join['type']} `{$join['table']}` ON `{$join['column1']}` = `{$join['column2']}`";
    }

    // Add the where clauses to the query if there are any.
    if (!empty($this->wheres)) {
      $query .= ' WHERE ';
      $wheres = [];
      // Add the where clauses to the query, e.g. column1 = ? AND column2 > ?.
      foreach ($this->wheres as $where) {
        $wheres[] = "`{$where['column']}` {$where['operator']} ?";
      }

      // Add the where clauses to the query, e.g. select * from table where column1 = ? AND column2 > ?.
      $query .= implode(' AND ', $wheres);
    }

    // Add the order by clauses to the query if there are any.
    if (!empty($this->orders)) {
      $query .= ' ORDER BY ';
      $orders = [];
      // Add the order by clauses to the query, e.g. column1 ASC, column2 DESC.
      foreach ($this->orders as $order) {
        $orders[] = "`{$order['column']}` {$order['direction']}";
      }

      // Add the order by clauses to the query,
      // e.g. select * from table where column1 = ? AND column2 > ? order by column1 ASC, column2 DESC.
      $query .= implode(', ', $orders);
    }

    // Add the limit and offset to the query if they are set.
    if (isset($this->limit)) {
      $query .= " LIMIT $this->limit";
    }

    if (isset($this->offset)) {
      $query .= " OFFSET $this->offset";
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
