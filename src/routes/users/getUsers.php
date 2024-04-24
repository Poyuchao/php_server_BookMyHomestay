<?php

require_once 'utils/send-response.php';
require_once 'structures/Route.php';

$GET_USERS = (new RouteBuilder())
  ->setMethod('GET')
  ->setPath('/users')
  ->setHandler(function () {
    send_response([
      [
        'id' => 1,
        'name' => 'John Doe',
      ],
      [
        'id' => 2,
        'name' => 'Jane Doe',
      ],
    ]);
  })
  ->build();

$GET_USER = (new RouteBuilder())
  ->setMethod('GET')
  ->setPath('/users/:id')
  ->setHandler(function ($params) {
    send_response([
      'id' => $params['id'],
      'name' => 'John Doe',
    ]);
  })
  ->build();
