<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/filter-users.php';
require_once ROOT . 'structures/index.php';

$GET_USERS = Route::path('/users')
  ->setMethod('GET')
  ->setAdmin() // This route is only accessible by admins
  ->setHandler(function ($_, Database $database) {
    $queryBuilder = QueryBuilder::create($database->connection)
      ->select()
      ->from('users');

    if (isset($_GET['search'])) {
      $queryBuilder->where('fname', 'LIKE', '%' . $_GET['search'] . '%')
        ->orWhere('lname', 'LIKE', '%' . $_GET['search'] . '%')
        ->orWhere('email', 'LIKE', '%' . $_GET['search'] . '%');
    }

    $users = $queryBuilder->execute();
    foreach ($users as &$user) {
      $user = filtersUser($user);

      $user['likes'] = QueryBuilder::create($database->connection)
        ->select(['homestays.*'])
        ->from('homestays')
        ->innerJoin('users_likes', 'homestays.id', 'users_likes.homestay_id')
        ->where('users_likes.user_id', '=', $user['id'])
        ->execute();
    }

    send_response($users);
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

    if (!$user) {
      send_error_response('User not found', 404);
    }

    send_response($user);
  })
  ->build();
