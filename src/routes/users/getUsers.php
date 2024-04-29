<?php

require_once 'database/QueryBuilder.php';
require_once 'utils/send-response.php';
require_once 'structures/index.php';

$GET_USERS = Route::path('/users')
  ->setMethod('GET')
  ->setHandler(function ($_, Database $database) {
    $queryBuilder = QueryBuilder::create($database->connection)
      ->select()
      ->from('users');

    if (isset($_GET['search'])) {
      $queryBuilder->where('fname', 'LIKE', '%' . $_GET['search'] . '%');
    }

    send_response($queryBuilder->execute());
  })
  ->build();

// Path parameters
$GET_USER = Route::path('/users/:id')
  ->setMethod('GET')
  ->setHandler(function (array $params, Database $database) {
    $user = QueryBuilder::create($database->connection)
      ->select()
      ->from('users')
      ->where('id', '=', $params['id'])
      ->first()
      ->execute();

    if (!$user) {
      send_error_response('User not found', 404);
    }

    send_response($user);
  })
  ->build();
