<?php

require_once getcwd() . "/" . 'constants.php';
require_once ROOT . 'utils/stdout-log.php';
require_once ROOT . 'router.php';
require_once ROOT . 'structures/Logger.php';

set_exception_handler(fn ($e) => Logger::globalError('Exception: ' . $e->getMessage()));
set_error_handler(fn ($errno, $errstr, $errfile, $errline) => Logger::globalError("Error($errno): $errstr in $errfile on line $errline"));

try {
  executeRequest();
} catch (Exception $e) {
  Logger::globalError('Exception: ' . $e->getMessage());
  send_error_response('Internal server error', 500);
}
