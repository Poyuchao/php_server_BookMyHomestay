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
        $regData = json_decode($json, true);
    

        // check if the json data is valid
        if (!$regData) {
            send_error_response('Invalid JSON', 400);
            return;
        }

         // Define required keys
         $requiredKeys = ['fname', 'lname', 'email', 'pass', 'gender', 'vegetarian', 'budget', 'location'];
        
         // Verify required keys
         if (!verifyRequiredKeys($regData, $requiredKeys)) {
             return; // The response is handled within the verifyRequiredKeys function
         }

        // Validate email
        if (!filter_var($regData['email'], FILTER_VALIDATE_EMAIL)) {
            send_error_response('Invalid email', 400);
            return;
        }
        
        // Validate password length
        if (strlen($regData['pass']) < 8) {
            send_error_response('Password too small', 400);
            return;
        }

        // Check for existing user
        $userWithSameEmail = QueryBuilder::create($database->connection)
            ->select()
            ->from('users')
            ->where('email', '=', $regData['email'])
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
                'email' => $regData['email'],
                'fname' => $regData['fname'],
                'lname' => $regData['lname'],
                'pass' => password_hash($regData['pass'], PASSWORD_BCRYPT, ['cost' => 12]),
                'gender' => $regData['gender'],
                'vegetarian' => $regData['vegetarian'],
                'budget' => $regData['budget'],
                'location' => $regData['location'],
            ])
            ->execute();

        send_response(['success' => "User created"], 201);
    })
    ->build();