<?php

function checkKeys($array, $keys)
{
  $array ??= [];
  foreach ($keys as $key) {
    if (!array_key_exists($key, $array)) {
      send_error_response('Arguments ' . implode(', ', $keys) . ' are required', 400);
    }
  }
}

// Check if all required keys are present in the data -> use for JSON data
function verifyRequiredKeys($data, $requiredKeys)
{
  foreach ($requiredKeys as $key) {
    if (!isset($data[$key])) {
      send_error_response("Argument $key is required", 400);
      return false; // Stop the execution if a key is missing
    }
  }
  return true; // All required keys are present
}
