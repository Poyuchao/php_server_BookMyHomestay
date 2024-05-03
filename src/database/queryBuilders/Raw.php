<?php

class RawQueryBuilder
{
  /**
   * The instance of the QueryBuilder class that created this instance.
   */
  private QueryBuilder $queryBuilder;
  /**
   * The raw query string.
   */
  private string $query;

  /**
   * The raw query string.
   */
  private array $bindings = [];

  /**
   * The constructor of the RawQueryBuilder class.
   */
  function __construct(QueryBuilder $queryBuilder, string $query)
  {
    $this->queryBuilder = $queryBuilder;
    $this->query = $query;
  }

  function setBindings(array $bindings): RawQueryBuilder
  {
    $this->bindings = $bindings;
    return $this;
  }

  /**
   * Execute the raw query.
   */
  function execute()
  {
    if (count($this->bindings) > 0) {
      $stmt = $this->queryBuilder->connection->prepare($this->query);

      $types = '';

      foreach ($this->bindings as $binding) {
        $types .= $this->queryBuilder->getBindValueType($binding);
      }

      $stmt->bind_param($types, ...$this->bindings);
      $stmt->execute();
      $result = $stmt->get_result();
      return $result->fetch_all(MYSQLI_ASSOC);
    }

    $result = $this->queryBuilder->connection->query($this->query);
    return $result->fetch_all(MYSQLI_ASSOC);
  }
}
