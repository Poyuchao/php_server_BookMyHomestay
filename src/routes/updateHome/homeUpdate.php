<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';


$addHomestay = Route::path('/addHome')
    ->setMethod('POST')
    ->setHandler(function($_, Database $database){
        
        $json = file_get_contents("php://input");

        $homeData = json_decode($json, true);

        if(!$homeData){
            send_error_response('Invalid JSON', 400);
            return;
        }

        $requiredKeys = ['title', 'desc', 'location', 'rating', 'price_per_month', 'amenities', 'vegetarian_friendly','image_path'];
        if(!verifyRequiredKeys($homeData, $requiredKeys)){
            return;
        }

        
        QueryBuilder::create($database->connection)
            ->insert()
            ->into('homestay')
            ->values([ 
                        'title'=>$homeData['title'], 
                        'desc'=>$homeData['desc'], 
                        'location'=>$homeData['location'],
                        'rating'=> $homeData['rating'],
                        'price_per_month'=> $homeData['price_per_month'],
                        'amenities'=> $homeData['amenities'], 
                        'vegetarian_friendly'=>$homeData['vegetarian_friendly'],
                     
            ])
            ->execute();

        QueryBuilder::create($database->connection)
            ->insert()
            ->into('home_images')
            ->values([
                'image_path'=>$homeData['image_path'],
                'homestay_id'=>QueryBuilder::create($database->connection)
                                ->select('id')
                                ->from('homestay')
                                ->where('title', '=', $homeData['title'])
                                ->first()
                                ->execute()
            ])
            ->execute();

})

->build();

