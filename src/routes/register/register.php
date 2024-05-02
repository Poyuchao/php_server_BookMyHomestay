<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$Register_user = Route::path('/reg')
    ->setMethod('POST')
    ->setHandler(function ($_, Database $database) {
        // Get JSON as a string from php://input
        $json = file_get_contents("php://input");

        // Decode the JSON string into an associative array
        $data = json_decode($json, true);
        print_r("here is register data ".$data['fname']);

        // check if the json data is valid
        if (!$data) {
            send_error_response('Invalid JSON', 400);
            return;
        }

         // Define required keys
         $requiredKeys = ['fname', 'lname', 'email', 'pass', 'gender', 'vegetarian', 'budget', 'location'];
        
         // Verify required keys
         if (!verifyRequiredKeys($data, $requiredKeys)) {
             return; // The response is handled within the verifyRequiredKeys function
         }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            send_error_response('Invalid email', 400);
            return;
        }
        
        // Validate password length
        if (strlen($data['pass']) < 8) {
            send_error_response('Password too small', 400);
            return;
        }

        // Check for existing user
        $userWithSameEmail = QueryBuilder::create($database->connection)
            ->select()
            ->from('users')
            ->where('email', '=', $data['email'])
            ->first()
            ->execute();
        
        if ($userWithSameEmail) {
            send_error_response('User with same email already exists', 400);
            return;
        }

        // Insert the new user
        QueryBuilder::create($database->connection)
            ->insert()
            ->into('users')
            ->values([
                'email' => $data['email'],
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'pass' => password_hash($data['pass'], PASSWORD_BCRYPT, ['cost' => 12]),
                'gender' => $data['gender'],
                'vegetarian' => $data['vegetarian'],
                'budget' => $data['budget'],
                'location' => $data['location'],
            ])
            ->execute();

        send_response(['success' => "User created"], 201);
    })
    ->build();