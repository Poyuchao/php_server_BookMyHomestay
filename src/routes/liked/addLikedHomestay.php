<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$POST_ADD_LIKED_HOMESTAY = Route::path('/liked/add')
  ->setMethod('POST')
  ->setAuthenticated()
  ->setHandler(function ($_, Database $database, $authUser) {
    checkKeys($_POST, ['homestay_id']);

    $hasLiked = QueryBuilder::create($database->connection)
      ->select()
      ->from('users_likes')
      ->where('user_id', '=', $authUser['id'])
      ->where('homestay_id', '=', $_POST['homestay_id'])
      ->first()
      ->execute();

    if ($hasLiked) {
      send_error_response('Homestay already liked', 400);
    }

    QueryBuilder::create($database->connection)
      ->insert()
      ->into('users_likes')
      ->values([
        'user_id' => $authUser['id'],
        'homestay_id' => $_POST['homestay_id']
      ])
      ->execute();

    send_response(['success' => 'Homestay liked'], 200);
  })
  ->build(); // This is the route object