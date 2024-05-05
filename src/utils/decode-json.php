<?php

require_once ROOT . 'utils/send-response.php';

function decodeBodyIfJson()
{
  if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    try {
      $_POST = json_decode(file_get_contents('php://input'), true);
    } catch (Exception $e) {
      send_error_response('Invalid JSON', 422);
    }
  }
}
