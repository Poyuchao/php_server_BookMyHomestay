<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$POST_DELETE_FAVORITE_HOMESTAY = Route::path('/favorite/delete')
  ->setMethod('POST')
  ->setAuthenticated()
  ->setHandler(function ($_, Database $database, $authUser) {
    checkKeys($_POST, ['homestay_id']);

    $homestay = QueryBuilder::create($database->connection)
      ->select()
      ->from('homestays')
      ->where('id', '=', $_POST['homestay_id'])
      ->first()
      ->execute();

    if (!$homestay) {
      send_error_response('Homestay not found', 404);
    }

    $hasLiked = QueryBuilder::create($database->connection)
      ->select()
      ->from('users_likes')
      ->where('user_id', '=', $authUser['id'])
      ->where('homestay_id', '=', $_POST['homestay_id'])
      ->first()
      ->execute();

    if (!$hasLiked) {
      send_error_response('Homestay not liked', 400);
    }

    QueryBuilder::create($database->connection)
      ->delete()
      ->from('users_likes')
      ->where('id', '=', $hasLiked['id'])
      ->execute();

    send_response(['success' => 'Homestay desliked'], 200);
  })
  ->build(); // This is the route object