<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$POST_REHASH = Route::path('/admin/rehash')
  ->setMethod('POST')
  ->setAdmin()
  ->setHandler(function ($_, Database $database) {
    $cost = $_POST['cost'] ?? 12;

    if (!is_numeric($cost)) {
      send_error_response('Cost must be a number', 400);
    }

    $allUsers = QueryBuilder::create($database->connection)
      ->select()
      ->from('users')
      ->execute();

    // For each users, get their password and convert it to a hash
    foreach ($allUsers as $user) {

      $password = $user['pass'];
      if (password_needs_rehash($password, PASSWORD_DEFAULT, ['cost' => $cost])) {
        $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
        QueryBuilder::create($database->connection)
          ->update()
          ->table('users')
          ->set(['pass' => $hash])
          ->where('id', '=', $user['id'])
          ->execute();
      }
    }

    send_response(['success' => 'All passwords have been rehashed']);
  })
  ->build(); // This is the route object