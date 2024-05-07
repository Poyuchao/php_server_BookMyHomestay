<?php

require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'structures/Logger.php';

function getSession(): array|null
{
  $logger = Logger::children('getSession');

  $sessionToken = $_COOKIE['PHPSESSID'] ?? null;

  $authHeader = $_SERVER['HTTP_X_TOKEN'] ?? apache_request_headers()['X-Token'] ?? null;

  if ($authHeader) {
    $sessionToken = str_replace('Bearer ', '', $authHeader);
  }

  if ($sessionToken) {
    session_id($sessionToken);
    session_start();

    $session = $_SESSION;

    Logger::globalInfo('Session: ' . json_encode($session));

    if (!isset($session['user'])) {
      $logger->debug('No user in session');
      return null;
    }

    if (!isset($session['timestamp'])) {
      $logger->debug('No timestamp in session');
      return null;
    }

    if (time() - $session['timestamp'] > SESSION_EXPIRATION) {
      $logger->debug('Session expired');
      return null;
    }

    $logger->info('Session found for user ' . $session['user']['email']);
    return $session;
  }

  $logger->debug('No session token');
  return null;
}
