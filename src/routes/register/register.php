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
            sendHttpCode(401,'Invalid JSON');
            return;
        }

         // Define required keys
         $requiredKeys = ['fname', 'lname', 'email', 'pass', 'gender', 'vegetarian', 'budget', 'location'];
        
         // Verify required keys
         if (!verifyRequiredKeys($regData, $requiredKeys)) {
            send_error_response('missing arguement', 400);
             return; // The response is handled within the verifyRequiredKeys function
         }

        // Validate email
        if (!filter_var($regData['email'], FILTER_VALIDATE_EMAIL)) {
            sendHttpCode(401,'Invalid email');
            return;
        }
        
        // Validate password length
        if (strlen($regData['pass']) < 8) {
            sendHttpCode(401,'Password needs to be at least 8 characters long');
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
            sendHttpCode(402,'User with same email already exists');
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
                'vegetarian' => $regData['vegetarian']=="yes"?1:0,
                'budget' => $regData['budget'],
                'location' => $regData['location'],
            ])
            ->execute();

        send_response(['success' => "User created"], 201);
    })
    ->build();