<?php

class RouteBuilder
{
  private $route;

  public function __construct()
  {
    $this->route = new Route();
  }

  public function setMethod(string $method)
  {
    $this->route->method = $method;
    return $this;
  }

  public function setAuthenticated()
  {
    $this->route->isAuthenticated = TRUE;
    return $this;
  }

  public function setAdmin()
  {
    $this->route->isAuthenticated = TRUE;
    $this->route->isAdmin = TRUE;
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
