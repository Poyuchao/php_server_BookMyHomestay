<?php
require_once 'routes/users/routes.php';
require_once 'utils/send-response.php';
require_once 'database/index.php';

$ROUTES = [
  'GET' => [
    $GET_USERS,
    $GET_USER,
  ],
  'POST' => [
    $POST_USERS,
    $PATCH_USER,
  ],
];

function executeRequest()
{
  global $ROUTES;

  $method = $_SERVER['REQUEST_METHOD'];
  $path = $_SERVER['REQUEST_URI'];

  if (!isset($ROUTES[$method])) {
    send_error_response('Method not allowed', 405);
    return;
  }

  $route = $ROUTES[$method];

  for ($i = 0; $i < count($route); $i++) {
    $routeMatch = $route[$i]->isMatch($method, $path);

    if ($routeMatch['isMatch']) {
      $database = new Database();

      $route[$i]->handler->__invoke($routeMatch['params'], $database);
      return;
    }
  }

  send_error_response('Route not found', 404);
}
