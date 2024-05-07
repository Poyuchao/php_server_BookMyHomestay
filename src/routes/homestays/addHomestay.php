<?php


require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';
require_once ROOT . 'structures/fileHandle.php';
require_once ROOT . 'utils/config.php';

$addHomestay = Route::path('/addHome')
  ->setMethod('POST')
  ->setAuthenticated()
  ->setHandler(function ($_, Database $database) {


    //after the user is logged in, the user can add a homestay ,
    //tempory user id is set to 1 because the login is not implemented
    // if (isset($_SESSION['user_id'])) {
    //     $logged_in_user_id = $_SESSION['user_id'];
    //     $logged_in_username = $_SESSION['username'];
    // } else {
    //     send_error_response("User not logged in", 401);
    //     return ;
    // }

    // Construct the path relative to this script's location

    checkKeys($_POST, ['title', 'desc', 'location', 'price_per_month', 'vegetarian_friendly', 'amenities']);

    $logged_in_user_id = $_SESSION['user']['id'];

    //verify the title is not shorter than 5 characters and contains only letters and spaces and not longer than 50 characters
    if (!preg_match('/^[a-zA-Z\s]{5,50}$/', $_POST['title'])) {
      sendHttpCode(401, 'Title must be between 5 and 50 characters and contain only letters and spaces allowed');
      return;
    }

    //verify the desc is not shorter than 10 characters
    if (strlen($_POST['desc']) < 10) {
      sendHttpCode(401, 'Description must be at least 20 characters');
      return;
    }


    //verify the price is a number
    if (!is_numeric($_POST['price_per_month'])) {
      sendHttpCode(401, 'Please enter a numeric value for the price.');
      return;
    }

    $amenities = explode(',', $_POST['amenities']); // Split the string into an array
    $homeData = $_POST; // get the homestay data from the form

    // check file is uploaded
    if (!isset($_FILES['imageFile'])) {
      echo "No file uploaded.";
      sendHttpCode(401, 'Please upload an image file.');
    }



    // Upload the file   
    $fileUpload = new FileUpload($_FILES['imageFile'], ROOT . HOMESTAY_IMG_FOLDER, MAX_FILE_SIZE);
    $img_addr = $fileUpload->commitUpload();
    // print_r("all good");
    // print_r("upload result: ".$img_addr);
    // $img_addr = HOMESTAY_IMG_DIR. DIRECTORY_SEPARATOR . basename($file['name']);



    //print_r("all good");


    // insert the rest of the homestay data into the homestay table
    $createdHomestay = QueryBuilder::create($database->connection)
      ->insert()
      ->into('homestays')
      ->values([
        'owner_id' => $logged_in_user_id,
        'title' => $homeData['title'],
        'desc' => $homeData['desc'],
        'location' => $homeData['location'],
        'rating' => TMP_RATING, //  define in config.php
        'price_per_month' => $homeData['price_per_month'],
        'vegetarian_friendly' => $homeData['vegetarian_friendly'] == "yes" ? 1 : 0,
      ])
      ->returning(['*'])
      ->execute();

    foreach ($amenities as $amenity) {

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


    //insert the image path into the homestay_images table
    QueryBuilder::create($database->connection)
      ->insert()
      ->into('homestay_images')
      ->values([
        'homestay_id' => $createdHomestay['id'],
        'image_path' => $img_addr,
      ])
      ->execute();

    send_response(['success' => "homestay created"], 201);
  })
  ->build();
