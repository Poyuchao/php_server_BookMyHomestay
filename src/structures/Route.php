<?php

require_once 'utils/stdout-log.php';
require_once 'utils/send-response.php';


//
class Route
{
  public ?string $method = NULL; // GET, POST, PUT, DELETE
  public ?string $path = NULL;   // The URL path for the route
  public array $routeParts = []; // An array to store segments of the path
  public $handler;               // A callable that handles the route


  //The constructor initializes the handler with a default function that sends a "405 Method Not Implemented" 
  //error if no specific handler is assigned later. 
  //This ensures that there's always a fallback handler to execute if routing configuration is incomplete.
  public function __construct()
  {
    $this->handler = function () {
      send_error_response('Route handler not implemented', 405);
    };
  }



  public function isMatch($method, $path)
  {
    //Initializes an empty array $parameters to store parameters extracted from the path.
    $parameters = [];

    // checks if the request's method matches the route's method. If not, 
    //it logs the mismatch and returns false for the match, along with an empty parameters array.
    if ($this->method !== $method) {
      debugLog('[' . $this->__toString() . "] Method does not match: $this->method !== $method");
      return [
        'isMatch' => FALSE,
        'params' => []
      ];
    }

    // Split path into parts by '/'
    $pathParts = explode('/', $path);

    //checks if the request's method matches the route's method. 
    //If not, it logs the mismatch and returns false for the match, along with an empty parameters array.
    $pathParts = array_filter($pathParts, function ($value) {
      return $value !== '' && $value !== 'index.php';
    });
    // Re-index array, because php stupidly doesn't do this by default,
    // Re-indexes the array to ensure keys are sequential after filtering.
    $pathParts = array_values($pathParts);

    //Compares the number of segments in the path with the number of segments in the predefined route.
    //If they don't match, it logs the mismatch and returns false
    $routeParts = $this->routeParts;

    debugLog('Path parts: ' . json_encode($pathParts));
    debugLog('Route parts: ' . json_encode($routeParts));

    // Check if path has the same number of parts as the route
    if (count($pathParts) !== count($routeParts)) {
      debugLog('[' . $this->__toString() . "] Path parts do not match: " . count($pathParts) . ' !== ' . count($routeParts));
      return [
        'isMatch' => FALSE,
        'params' => []
      ];;
    }

    // Check if each part of the path matches the route
    for ($i = 0; $i < count($pathParts); $i++) {
      // Check if part is a parameter, and if so, add it to the parameters array
      if (isset($routeParts[$i][0]) && $routeParts[$i][0] == ':') {
        $parameters[substr($routeParts[$i], 1)] = $pathParts[$i];
        continue;
      }

      // Check if part is not a parameter and does not match the route
      if ($pathParts[$i] !== $routeParts[$i]) {
        return [
          'isMatch' => FALSE,
          'params' => []
        ];;
      }
    }

    stdoutLog('[' . $this->__toString() . '] Executing handler');

    // Return parameters if route matches
    return ['isMatch' => TRUE, 'params' => $parameters];
  }

  public function __toString()
  {
    return $this->method . ' ' . $this->path;
  }
}

class RouteBuilder
{
  private $route;

  public function __construct()
  {
    $this->route = new Route();
  }

  public function setMethod(string $method) //
  {
    $this->route->method = $method;
    return $this;
  }

  public function setPath(string $path)
  {
    $routeParts = explode('/', $path);
    $routeParts = array_filter($routeParts, function ($value) {
      return $value !== '';
    });
    $routeParts = array_values($routeParts);
    $this->route->routeParts = $routeParts;
    $this->route->path = $path;
    return $this;
  }

  public function setHandler(callable $handler)
  {
    $this->route->handler = $handler;
    return $this;
  }

  public function build()
  {
    if ($this->route->method === NULL) {
      throw new Exception('Method is required');
    }

    if ($this->route->path === NULL) {
      throw new Exception('Path is required');
    }

    return $this->route;
  }
}
