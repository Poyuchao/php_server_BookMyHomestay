<?php

function checkKeys($array, $keys)
{
  foreach ($keys as $key) {
    if (!array_key_exists($key, $array)) {
      send_error_response('Arguments ' . implode(', ', $keys) . ' are required', 400);
    }
  }
}
