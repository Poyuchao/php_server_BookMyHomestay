<?php

define('ROOT', getcwd() . "/");


ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once ROOT . 'utils/stdout-log.php';
require_once ROOT . 'router.php';

try {
  executeRequest();
} catch (Exception $e) {
  stdoutLog('Exception: ' . $e->getMessage());
  send_error_response('Internal server error', 500);
}
