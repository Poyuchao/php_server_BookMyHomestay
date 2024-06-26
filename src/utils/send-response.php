<?php
function send_error_response($message, $status_code, $die = true)
{
  if (!headers_sent()) {
    header('Content-Type: application/json');
  }
  http_response_code($status_code);
  echo json_encode(['error' => $message]);

  if ($die) {
    die();
  }
}

// Send a JSON response with the provided data
function send_response($data, $status_code = 200)
{
  if (!headers_sent()) {
    header('Content-Type: application/json');
  }
  http_response_code($status_code);
  echo json_encode($data);
}


function send_non_data_response()
{
  http_response_code(204);
}



function sendHttpCode($code, $message, $die_flag = false)
{
  http_response_code($code);

  if ($code >= 400) {
    $message = json_encode(['error' => $message]);
  }

  if ($die_flag) {
    die($message);
  } else {
    echo $message;
  }
}
