<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/filter-users.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$POST_UNLOCK_ACCOUNT = Route::path('/admin/unlockAccount')
  ->setMethod('POST')
  ->setAdmin()
  ->setHandler(function ($_, Database $database) {
    checkKeys($_POST, ['user_id']);

    $user = QueryBuilder::create($database->connection)
      ->update()
      ->table('users')
      ->set(['failed_attempts' => 5])
      ->where('id', '=', $_POST['user_id'])
      ->returning(['*'])
      ->execute();

    send_response(['user' => filtersUser($user[0])]);
  })
  ->build(); // This is the route object