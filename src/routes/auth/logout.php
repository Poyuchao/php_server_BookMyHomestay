<?php

require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'structures/index.php';

$POST_LOGOUT = Route::path('/logout')
  ->setMethod('POST')
  ->setAuthenticated()
  ->setHandler(function ($_, $database) {
    session_destroy();
    send_response(['success' => 'Logged out'], 200);
  })
  ->build();
