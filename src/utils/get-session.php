<?php

require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'structures/Logger.php';

function getSession(): array|null
{
  // Created a logger for this function
  $logger = Logger::children('getSession');

  // Try to get the session token from the cookie
  $sessionToken = $_COOKIE['PHPSESSID'] ?? null;

  //Try to get the session token from the header X-Token
  $authHeader = $_SERVER['HTTP_X_TOKEN'] ?? apache_request_headers()['X-Token'] ?? null;

  if ($authHeader) {
    // Remove the Bearer prefix if the token is in the header
    $sessionToken = str_replace('Bearer ', '', $authHeader);
  }

  // If the session token exists, start the session
  if ($sessionToken) {
    session_id($sessionToken);
    session_start();

    // Get the session data
    $session = $_SESSION;

    $logger->info('Session: ' . json_encode($session));

    // Check if the session has the required keys
    if (!isset($session['user'])) {
      session_unset();
      session_destroy();
      $logger->debug('No user in session');
      return null;
    }

    if (!isset($session['timestamp'])) {
      session_unset();
      session_destroy();
      $logger->debug('No timestamp in session');
      return null;
    }

    // Check if the session has expired
    if (time() - $session['timestamp'] > SESSION_EXPIRATION) {
      session_unset();
      session_destroy();
      $logger->debug('Session expired');
      return null;
    }

    $logger->info('Session found for user ' . $session['user']['email']);
    return $session;
  }

  // If the session token does not exist, return null
  $logger->debug('No session token');
  return null;
}
