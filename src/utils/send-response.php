<?php

function send_error_response($message, $status_code, $die = true)
{
  header('Content-Type: application/json');
  http_response_code($status_code);
  echo json_encode(['error' => $message]);

  if ($die) {
    die();
  }
}

// Send a JSON response with the provided data
function send_response($data, $status_code = 200)
{
  header('Content-Type: application/json');
  http_response_code($status_code);
  echo json_encode($data);
}


function send_non_data_response()
{
  header('Content-Type: application/json');
  http_response_code(204);
}
