<?php
require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'structures/Route.php';
require_once ROOT . 'structures/person.php';
require_once ROOT . 'utils/function.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'utils/config.php';


$POST_LOGIN = Route::path('/login')
    ->setMethod('POST')
    ->setHandler(function ($_, Database $database) {
        // Check if the required keys are in the request
        checkKeys($_POST, ["email", "pass"]);

        // Getting the data from the request
        $email = $_POST['email'];
        $pass = $_POST['pass'];

        try {
            // Create a new person object
            $person = new Person($email);
            // Authenticate the user
            $jsonUser = $person->authenticate($pass, $database->connection);

            // print_r($jsonUser);
            send_response([
                'user' => $jsonUser,
                'session' => session_id()
            ], 201);
        } catch (Exception $e) {
            send_error_response('Login failed', 400);
        }
    })

    ->build();
