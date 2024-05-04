<?php


require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';

$addHomestay = Route::path('/addHome')
    ->setMethod('POST')
    ->setHandler(function($_, Database $database){

       
        //after the user is logged in, the user can add a homestay ,
        //tempory user id is set to 1 because the login is not implemented
        // if (isset($_SESSION['user_id'])) {
        //     $logged_in_user_id = $_SESSION['user_id'];
        //     $logged_in_username = $_SESSION['username'];
        // } else {
        //     send_error_response("User not logged in", 401);
        //     return;
        // }

        $logged_in_user_id = 1; //tempory user id is set to 1 because the login is not implemented

        $json = file_get_contents("php://input"); // Get JSON as a string from php://input

        $homeData = json_decode($json, true);

        if(!$homeData){
            send_error_response('Invalid JSON', 400);
            return;
        }
        
        print_r("amenities".$homeData['title']);
        $requiredKeys = ['title','desc', 'location', 'price_per_month', 'amenities', 'vegetarian_friendly','image_path'];
        if(!verifyRequiredKeys($homeData, $requiredKeys)){
            send_error_response('missing arguement', 400);
            return;
        }

        print_r("all good");

        $homeTmpRating = 3; //tempory rating is set to 3 because the review system is not implemented

  
        // insert the rest of the homestay data into the homestay table  
        QueryBuilder::create($database->connection)
        ->insert()
        ->into('homestays')
        ->values([ 
                    'owner_id'=>$logged_in_user_id,
                    'title'=>$homeData['title'], 
                    'desc'=>$homeData['desc'], 
                    'location'=>$homeData['location'],
                    'rating'=> $homeTmpRating,
                    'price_per_month'=> $homeData['price_per_month'],
                    'vegetarian_friendly'=>$homeData['vegetarian_friendly'] == "yes" ? 1 : 0,
                
        ])
        
        ->execute();
        
        print_r("all good");

        //get the homestay_id of the homestay that was just inserted
        $homestay_id = $database->connection->insert_id;

        //Currently the homestays_amenities table is empty , so when the user adds a homestay, 
        //the amenities are added to the amenities table, amenities is a array -> amenities: ['wifi', 'parking', 'kitchen']
        //Loop through each amenity and insert if not exists

        //build relation between homestay_id and amenities_id in homestay_amenities table
        foreach ($homeData['amenities'] as $amenity) {

            print_r("each amenity: ".$amenity."\n");
            //check if the amenity exists in the amenities table
            $amenityExists = QueryBuilder::create($database->connection)
                ->select()
                ->from('amenities')
                ->where('name', '=', $amenity)
                ->first() // Get the first result
                ->execute();

            $amenity_id = $amenityExists ? $amenityExists['id'] : null;    

            //  if the amenity already exist, build relation between homestay_id and amenities_id in homestay_amenities table
            if ($amenityExists) {
              QueryBuilder::create($database->connection)
                ->insert()
                ->into('homestays_amenities')
                ->values([
                    'homestay_id' => $homestay_id,
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

                print_r("amenity id: ".$amenity_id."\n");
                print_r("homestay id: ".$homestay_id."\n");

                // build relation between homestay_id and amenities_id in homestay_amenities table
                 QueryBuilder::create($database->connection)
                 ->insert()
                 ->into('homestays_amenities')
                 ->values([
                     'homestay_id' => $homestay_id,
                     'amenities_id' => $amenity_id
                 ])
                 ->execute();  

            }
        }

   
        //insert the image path into the homestay_images table
        QueryBuilder::create($database->connection)
            ->insert()
            ->into('homestay_images')
            ->values([ 
                'homestay_id'=>$homestay_id,
                'image_path'=>$homeData['image_path'], 
            ])
          
            ->execute();

        send_response(['success' => "homestay created"], 201);
})

->build();

