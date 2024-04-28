<?php
require_once 'utils/stdout-log.php';
require_once 'router.php';

stdoutLog('Hello, world!');

try {
  executeRequest();
} catch (Exception $e) {
  stdoutLog('Exception: ' . $e->getMessage());
  send_error_response('Internal server error', 500);
}
