<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'structures/index.php';
require_once ROOT . 'utils/config.php';
$LOAD_USERS_FROMJSON =  Route::path('/loadUsers')
->setMethod('GET')
->setHandler(function ($_, Database $database) {
    $filePath = DATA_ROUTE . "/user.json";
    $file = fopen($filePath, "r") or die("Unable to open file!");
    
    if (filesize($filePath) > 0) {
        $data = fread($file, filesize($filePath));
        fclose($file); 
        $users = json_decode($data, true); // Decode as an associative array

        if (is_array($users)) {
            foreach ($users as $user) {
                QueryBuilder::create($database->connection)
                    ->insert()
                    ->into('users')
                    ->values([
                        'email' => $user['email'],
                        'fname' => $user['fname'],
                        'lname' => $user['lname'],
                        'pass' => $user['pass'], 
                        'gender' => $user['gender'],
                        'vegetarian' => $user['vegetarian'] ? 1 : 0,
                        'budget' => $user['budget'],
                        'location' => $user['location'],
                        'type' =>$user['type']
                    ])
                    ->execute();
            }
        } else {
            die("Error: Unable to parse users data as an array.");
        }
    } else {
        fclose($file);
        die("Error: File is empty.");
    }
})
->build(); // This builds and registers the route object


// $LOAD_HOMESTAYS_FROMJSON =  Route::path('/loadHomestays')
// ->setMethod('GET')
// ->setHandler(function ($_, Database $database) {
//     $filePath = DATA_ROUTE . "/homestay.json";
//     $file = fopen($filePath, "r") or die("Unable to open file!");
    
//     if (filesize($filePath) > 0) {
//         $data = fread($file, filesize($filePath));
//         fclose($file); 
//         $homestays = json_decode($data, true); // Decode as an associative array

//         if (is_array($homestays)) {
//             foreach ($homestays as $homestay) {
//                 QueryBuilder::create($database->connection)
//                     ->insert()
//                     ->into('homestays')
//                     ->values([
//                         'owner_id' => $homestay['owner_id'],
//                         'title' => $homestay['title'],
//                         'desc' => $homestay['desc'],
//                         'location' => $homestay['location'],
//                         'rating' => $homestay['rating'],
//                         'price_per_month' => $homestay['price_per_month'],
//                         'vegetarian_friendly' => $homestay['vegetarian_friendly'] ? 1 : 0
//                     ])
//                     ->execute();
//             }
//         } else {
//             die("Error: Unable to parse homestays data as an array.");
//         }
//     } else {
//         fclose($file);
//         die("Error: File is empty.");
//     }
// })