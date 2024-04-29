<?php

require_once 'utils/stdout-log.php';
require_once 'utils/send-response.php';

class Route
{
  public ?string $method = NULL;
  public ?string $path = NULL;
  public array $routeParts = [];
  public bool $isAuthenticated = FALSE;
  public bool $isAdmin = FALSE;
  public $handler;

  public function __construct()
  {
    $this->handler = function () {
      send_error_response('Route handler not implemented', 405);
    };
  }

  public function isMatch($method, $path)
  {
    $parameters = [];

    // Check if method matches
    if ($this->method !== $method) {
      debugLog('[' . $this->__toString() . "] Method does not match: $this->method !== $method");
      return [
        'isMatch' => FALSE,
        'params' => []
      ];
    }

    // Split path into parts by '/'
    $pathParts = explode('/', $path);
    $pathParts = array_filter($pathParts, function ($value) {
      return $value !== '' && $value !== 'index.php';
    });
    // Re-index array, because php stupidly doesn't do this by default
    $pathParts = array_values($pathParts);

    $lastItem = end($pathParts);

    if (str_contains($lastItem, '?')) {
      $pathPlusQuery = explode('?', $lastItem);
      $pathParts[count($pathParts) - 1] = $pathPlusQuery[0];
    }

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

  public static function path($path)
  {
    return (new RouteBuilder())->setPath($path);
  }
}
