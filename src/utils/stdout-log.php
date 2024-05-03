<?php


function stdoutLog($msg)
{
  $handler = fopen('php://stdout', 'w');
  fwrite($handler, $msg . PHP_EOL);
  fclose($handler);
}

function debugLog($msg)
{
  if (getenv('ENV') !== 'PROD') {
    stdoutLog($msg);
  }
}
