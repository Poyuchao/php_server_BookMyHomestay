<?php

require_once 'database/QueryBuilder.php';
require_once 'utils/send-response.php';
require_once 'structures/index.php';

$GET_USERS = (new RouteBuilder())
  ->setMethod('GET')
  ->setPath('/users')
  ->setHandler(function ($_, $database) {
    $take = $_GET['take'] ?? 1000;
    $skip = $_GET['skip'] ?? 0;

    $users = (new QueryBuilder($database->connection))
      ->select()
      ->from('users')
      ->limit($take)
      ->offset($skip)
      ->execute();
    send_response($users);
  })
  ->build();

$GET_USER = (new RouteBuilder())
  ->setMethod('GET')
  ->setPath('/users/:id')
  ->setHandler(function ($params, $database) {
    $user = (new QueryBuilder($database->connection))
      ->select()
      ->from('users')
      ->where('id', '=', $params['id'])
      ->execute();

    if (count($user) === 0) {
      send_error_response('User not found', 404);
    }

    send_response($user[0]);
  })
  ->build();
