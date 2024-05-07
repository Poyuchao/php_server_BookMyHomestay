<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$GET_LIKED_HOMESTAYS = Route::path('/favorite')
  ->setMethod('GET')
  ->setAuthenticated()
  ->setHandler(function ($_, Database $database, $authUser) {
    $liked = QueryBuilder::create($database->connection)
      ->select(['homestays.*'])
      ->from('homestays')
      ->innerJoin('users_likes', 'homestays.id', 'users_likes.homestay_id')
      ->where('users_likes.user_id', '=', $authUser['id'])
      ->execute();

    send_response($liked);
  })
  ->build(); // This is the route object