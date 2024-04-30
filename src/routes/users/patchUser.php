<?php

require_once 'database/QueryBuilder.php';
require_once 'utils/send-response.php';
require_once 'utils/check-keys.php';
require_once 'structures/index.php';

$PATCH_USER = Route::path('/users/update/:id')
  ->setMethod('POST')
  ->setHandler(function (array $params, Database $database) {
    $data = [];

    if (isset($_POST['fname'])) {
      $data['fname'] = $_POST['fname'];
    }

    if (isset($_POST['lname'])) {
      $data['lname'] = $_POST['lname'];
    }

    if (isset($_POST['email'])) {
      if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        send_error_response('Invalid email', 400);
      }

      $userWithEmail = QueryBuilder::create($database->connection)
        ->select()
        ->from('users')
        ->where('email', '=', $_POST['email'])
        ->first()
        ->execute();

      if ($userWithEmail) {
        send_error_response('Email already in use', 400);
      }

      $data['email'] = $_POST['email'];
    }

    if (isset($_POST['pass'])) {
      if (strlen($_POST['pass']) < 8) {
        send_error_response('Password too small', 400);
      }

      $data['pass'] = password_hash($_POST['pass'], PASSWORD_BCRYPT);
    }

    if (isset($_POST['gender'])) {
      $data['gender'] = $_POST['gender'];
    }

    if (isset($_POST['vegetarian'])) {
      $data['vegetarian'] = $_POST['vegetarian'];
    }

    if (isset($_POST['budget'])) {
      $data['budget'] = $_POST['budget'];
    }

    if (isset($_POST['location'])) {
      $data['location'] = $_POST['location'];
    }

    if (count($data) === 0) {
      send_error_response('No data to update', 400);
    }

    $userToUpdate = QueryBuilder::create($database->connection)
      ->select()
      ->from('users')
      ->where('id', '=', $params['id'])
      ->first()
      ->execute();

    if (!$userToUpdate) {
      send_error_response('User not found', 404);
    }

    QueryBuilder::create($database->connection)
      ->update()
      ->table('users')
      ->set($data)
      ->where('id', '=', $params['id'])
      ->execute();

    return send_response([
      'success' => 'User updated',
    ]);
  })
  ->build();
