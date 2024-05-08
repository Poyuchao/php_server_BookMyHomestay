<?php

function parseOrder($order, $allowedColumns, $default): array
{
  [$column, $direction] = explode('_', $order ?? $default);

  if (!in_array($column, $allowedColumns)) {
    return parseOrder($default, $allowedColumns, $default);
  }

  if (!in_array($direction, [QUERY_BUILDER_SORT_ASC, QUERY_BUILDER_SORT_DESC])) {
    return parseOrder($default, $allowedColumns, $default);
  }

  return [
    'column' => $column,
    'direction' => $direction
  ];
}
