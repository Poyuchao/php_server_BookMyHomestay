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
                            'pass' => password_hash($user['pass'], PASSWORD_BCRYPT, ["cost" => 12]),
                            'gender' => $user['gender'],
                            'vegetarian' => $user['vegetarian'] ? 1 : 0,
                            'budget' => $user['budget'],
                            'location' => $user['location'],
                            // 'type' => $user['type'] ?? 'user'
                        ])
                        ->execute();
                }
            } else {
                die("Error: Unable to parse users data as an array. ");
            }
        } else {
            fclose($file);
            die("Error: File is empty.");
        }
    })
    ->build(); // This builds and registers the route object


$LOAD_HOMESTAYS_FROMJSON =  Route::path('/loadHomestays')
    ->setMethod('GET')
    ->setHandler(function ($_, Database $database) {
        $filePath = DATA_ROUTE . "/homestay.json";
        $file = fopen($filePath, "r") or die("Unable to open file!");

        if (filesize($filePath) > 0) {
            $data = fread($file, filesize($filePath));
            fclose($file);
            $homestays = json_decode($data, true); // Decode as an associative array

            // insert title, desc, location, rating, price_per_month, vegetarian_friendly into the homestays table
            foreach ($homestays as $homestay) {
                $owner_id = rand(1, 25);
                QueryBuilder::create($database->connection)
                    ->insert()
                    ->into('homestays')
                    ->values([
                        'owner_id' => $owner_id, //random int 1-25 , simulating owner id 
                        'title' => $homestay['title'],
                        'desc' => $homestay['desc'],
                        'location' => $homestay['location'],
                        'rating' => $homestay['rating'],
                        'price_per_month' => $homestay['price_per_month'],
                        'vegetarian_friendly' => $homestay['vegetarian_friendly'] ? 1 : 0
                    ])
                    ->execute();
            }

            //  insert the amenities into the amenities table
            foreach ($homestays as $homestay) {

                foreach ($homestay['amenities'] as $amenity) {

                    //check if the amenity exists in the amenities table
                    $amenityExists = QueryBuilder::create($database->connection)
                        ->select()
                        ->from('amenities')
                        ->where('name', '=', $amenity)
                        ->first() // Get the first result
                        ->execute();

                    $amenity_id = $amenityExists ? $amenityExists['id'] : null;

                    $createdHomestay = QueryBuilder::create($database->connection)
                        ->select()
                        ->from('homestays')
                        ->where('title', '=', $homestay['title'])
                        ->first()
                        ->execute();

                    //  if the amenity already exist, build relation between homestay_id and amenities_id in homestay_amenities table
                    if ($amenityExists) {

                        QueryBuilder::create($database->connection)
                            ->insert()
                            ->into('homestays_amenities')
                            ->values([
                                'homestay_id' => $createdHomestay['id'],
                                'amenities_id' => $amenity_id
                            ])
                            ->execute();
                    }

                    //if the amenity does not exist, insert it into the amenities table
                    if (!$amenityExists) {

                        QueryBuilder::create($database->connection)
                            ->insert()
                            ->into('amenities')
                            ->values(['name' => $amenity])
                            ->execute();

                        // Retrieve the last inserted ID for the new amenity
                        $amenity_id = $database->connection->insert_id;

                        // build relation between homestay_id and amenities_id in homestay_amenities table
                        QueryBuilder::create($database->connection)
                            ->insert()
                            ->into('homestays_amenities')
                            ->values([
                                'homestay_id' => $createdHomestay['id'],
                                'amenities_id' => $amenity_id
                            ])
                            ->execute();
                    }
                }
            }




            // }

            foreach ($homestays as $homestay) {
                $createdHomestay = QueryBuilder::create($database->connection)
                    ->select()
                    ->from('homestays')
                    ->where('title', '=', $homestay['title'])
                    ->first()
                    ->execute();

                QueryBuilder::create($database->connection)
                    ->insert()
                    ->into('homestay_images')
                    ->values([
                        'homestay_id' => $createdHomestay['id'],
                        'image_path' => str_replace('/img', 'http://localhost/homestayImg', $homestay['image_path'])
                    ])
                    ->execute();
            }
        } else {
            fclose($file);
            die("Error: File is empty.");
        }
    })
    ->build();
