<?php
require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$POST_USERS = Route::path('/users')
  ->setMethod('POST')
  ->setHandler(function ($_, Database $database) {
    checkKeys($_POST, ['fname', 'lname', 'email', 'pass', 'gender', 'vegetarian', 'budget', 'location']);

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      send_error_response('Invalid email', 400);
    }

    if (strlen($_POST['pass']) < 8) {
      send_error_response('Password too small', 400);
    }

    $userWithSameEmail = QueryBuilder::create($database->connection)
      ->select()
      ->from('users')
      ->where('email', '=', $_POST['email'])
      ->first()
      ->execute();

    if ($userWithSameEmail) {
      send_error_response('User with same email already exists', 400);
    }

    QueryBuilder::create($database->connection)
      ->insert()
      ->into('users')
      ->values([
        'email' => $_POST['email'],
        'fname' => $_POST['fname'],
        'pass' => password_hash($_POST['pass'], PASSWORD_BCRYPT),
        'lname' => $_POST['lname'],
        'gender' => $_POST['gender'],
        'vegetarian' => $_POST['vegetarian'],
        'budget' => $_POST['budget'],
        'location' => $_POST['location'],
      ])
      ->execute();

    send_response([
      'success' => "User created",
    ], 201);
  })
  ->build();
