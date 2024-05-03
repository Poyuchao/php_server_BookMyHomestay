<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'structures/index.php';

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
  ->build(); // This is the route object

// Path parameters
$GET_USER = Route::path('/users/:id_or_email')
  ->setMethod('GET')
  ->setHandler(function (array $params, Database $database) {
    $user = QueryBuilder::create($database->connection)
      ->select()
      ->from('users')
      ->where('id', '=', $params['id_or_email'])
      ->orWhere('email', '=', $params['id_or_email'])
      ->first()
      ->execute();

    $user = QueryBuilder::create($database->connection)
      ->raw('SELECT * FROM users WHERE id = ? OR email = ?')->setBindings([
        $params['id_or_email'],
        $params['id_or_email']
      ])->execute();

    if (!$user) {
      send_error_response('User not found', 404);
    }

    send_response($user);
  })
  ->build();
