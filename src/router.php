<?php

require_once ROOT . 'routes/users/routes.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'database/index.php';
require_once ROOT . 'routes/login/login.php'; 
require_once ROOT . 'routes/register/register.php';

$ROUTES = [   
  'GET' => [
    
    $GET_USERS, //The $GET_USERS variable is an instance of the Route class that defines the route for getting all users.
    $GET_USER,  //The $GET_USER variable is an instance of the Route class that defines the route for getting a specific user.
  ],
  'POST' => [
    $Register_user,
    $PATCH_USER
   
  ],
];

//handle HTTP requests in a PHP application.
function executeRequest()
{
 
   // Set CORS headers
   header("Access-Control-Allow-Origin: *");
   header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
   header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
   // Handle preflight CORS requests
   if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
       // Return status 200 OK
       header("HTTP/1.1 200 OK");
       return;
   }
   
  global $ROUTES; 
  
  $method = $_SERVER['REQUEST_METHOD']; // GET, POST, PUT, DELETE
  $path = $_SERVER['REQUEST_URI'];       

  $method = $_SERVER['REQUEST_METHOD'];
  $path = $_SERVER['REQUEST_URI'];
  $path = explode('index.php', $path)[1];

  if (!isset($ROUTES[$method])) {
    send_error_response('Method not allowed', 405);
    return;
  }

  $route = $ROUTES[$method]; //get the routes for the specific HTTP method from the $ROUTES array.


  //iterates over each route in the $route array and checks if the route matches the requested method and path.
  for ($i = 0; $i < count($route); $i++) {
    //checks if the route matches the requested method and path using the isMatch method of the Route class.
    $routeMatch = $route[$i]->isMatch($method, $path);

    if ($routeMatch['isMatch']) {
      $database = new Database();

      $route[$i]->handler->__invoke($routeMatch['params'], $database);
      return;
    }
  }

  send_error_response('Route not found', 404);
}
