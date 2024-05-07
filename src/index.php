<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

set_exception_handler(function ($e) {
  Logger::globalError('Exception: ' . $e->getMessage());
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
  Logger::globalError("Error($errno): $errstr in $errfile on line $errline");
});

require_once getcwd() . "/" . 'constants.php';
require_once ROOT . 'utils/stdout-log.php';
require_once ROOT . 'router.php';
require_once ROOT . 'structures/Logger.php';

try {
  executeRequest();
} catch (Exception $e) {
  Logger::globalError("Error(" . $e->getCode() . "): " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
  send_error_response('Internal server error', 500);
}
