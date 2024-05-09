<?php

require_once ROOT . 'routes/users/routes.php';
require_once ROOT . 'routes/liked/routes.php';
require_once ROOT . 'routes/auth/routes.php';
require_once ROOT . 'routes/homestays/routes.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'database/index.php';
require_once ROOT . 'routes/admin/rehash.php';
require_once ROOT . 'routes/admin/unlockAccount.php';
require_once ROOT . 'routes/loadJson/loadJson.php';
require_once ROOT . 'utils/decode-json.php';
require_once ROOT . 'utils/get-session.php';
require_once ROOT . 'structures/Logger.php';



$ROUTES = [
  'GET' => [
    $LOAD_HOMESTAYS_FROMJSON, //The $LOAD_HOMESTAYS_FROMJSON variable is an instance of the Route class that defines the route for loading homestays from a JSON file.
    $LOAD_USERS_FROMJSON, //The $LOAD_USERS_FROMJSON variable is an instance of the Route class that defines the route for loading users from a JSON file.
    $GET_USERS, //The $GET_USERS variable is an instance of the Route class that defines the route for getting all users.
    $GET_USER,  //The $GET_USER variable is an instance of the Route class that defines the route for getting a specific user.
    $GET_LIKED_HOMESTAYS, //The $GET_LIKED_HOMESTAYS variable is an instance of the Route class that defines the route for getting liked homestays.
    $GET_HOMES, //The $GET_HOMES variable is an instance of the Route class that defines the route for getting homestays.
  ],
  'POST' => [
    $PATCH_USER,
    $addHomestay,
    $POST_LOGIN,
    $POST_REGISTER,
    $POST_LOGOUT,
    $POST_REHASH,
    $POST_UNLOCK_ACCOUNT,
    $POST_ADD_FAVORITE_HOMESTAY, //The $ADD_LIKED_HOMESTAY variable is an instance of the Route class that defines the route for adding a liked homestay.
    $POST_DELETE_FAVORITE_HOMESTAY, //The $DELETE_LIKED_HOMESTAY variable is an instance of the Route class that defines the route for deleting a liked homestay.
  ],
];

//handle HTTP requests in a PHP application.
function executeRequest()
{

  // Set CORS headers
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-Token");

  // Handle preflight CORS requests
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Return status 200 OK
    header(
      $_SERVER["SERVER_PROTOCOL"] . " 200 OK"
    );
    return;
  }

  // Decode JSON body if content type is application/json and set it to $_POST
  decodeBodyIfJson();

  global $ROUTES;

  $method = $_SERVER['REQUEST_METHOD']; // GET, POST, PUT, DELETE
  $path = $_SERVER['REQUEST_URI'];

  $method = $_SERVER['REQUEST_METHOD'];
  $path = $_SERVER['REQUEST_URI'];
  $path = explode('index.php', $path)[1];

  //checks if a specific HTTP method (represented by $method) is defined in the $ROUTES array. 
  //If the method is not defined, it sends an error response with a 405 status code (Method Not Allowed) and a message 'Method not allowed',
  //then it stops further execution of the function.
  if (!isset($ROUTES[$method])) {
    send_error_response('Method not allowed', 405);
    return;
  }

  $route = $ROUTES[$method]; //get the routes for the specific HTTP method from the $ROUTES array.

  $userSession = getSession();

  //iterates over each route in the $route  array and checks if the route matches the requested method and path.
  for ($i = 0; $i < count($route); $i++) {
    //checks if the route matches the requested method and path using the isMatch method of the Route class.
    $currentRoute = $route[$i];
    $routeMatch = $currentRoute->isMatch($method, $path);

    if ($routeMatch['isMatch']) {
      Logger::globalInfo('Route matched: ' . $currentRoute->path);
      if ($currentRoute->isAuthenticated && !$userSession) {
        send_error_response('Unauthorized', 401);
        return;
      }

      if ($currentRoute->isAdmin && !$userSession['user']['admin']) {
        send_error_response('Unauthorized', 401);
        return;
      }

      $database = new Database();
      $authUser = $userSession ? $userSession['user'] : null;

      $route[$i]->handler->__invoke($routeMatch['params'], $database, $authUser);
      return;
    }
  }

  send_error_response('Route not found', 404);
}
