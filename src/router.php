<?php
require_once ROOT . 'routes/users/routes.php';
require_once ROOT . 'routes/login/login.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'database/index.php';

$ROUTES = [
  'GET' => [

    $GET_USERS, //The $GET_USERS variable is an instance of the Route class that defines the route for getting all users.
    $GET_USER,  //The $GET_USER variable is an instance of the Route class that defines the route for getting a specific user.
  ],
  'POST' => [
    $POST_USERS,
    $PATCH_USER,
  ],
];

//handle HTTP requests in a PHP application.
function executeRequest()
{
  global $ROUTES;

  $method = $_SERVER['REQUEST_METHOD']; // GET, POST, PUT, DELETE
  $path = $_SERVER['REQUEST_URI'];

  //checks if a specific HTTP method (represented by $method) is defined in the $ROUTES array. 
  //If the method is not defined, it sends an error response with a 405 status code (Method Not Allowed) and a message 'Method not allowed',
  //then it stops further execution of the function.
  if (!isset($ROUTES[$method])) {
    send_error_response('Method not allowed', 405);
    return;
  }

  $route = $ROUTES[$method]; //get the routes for the specific HTTP method from the $ROUTES array.


  //iterates over each route in the $route  array and checks if the route matches the requested method and path.
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
