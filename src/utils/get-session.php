<?php

require_once ROOT . 'utils/send-response.php';

function getSession(): array|null
{
  $sessionCookie = $_COOKIE['PHPSESSID'] ?? null;

  if ($sessionCookie) {
    session_id($sessionCookie);
    session_start();

    $session = $_SESSION;

    if (!isset($session['user'])) {
      return null;
    }

    if (!isset($session['timestamp'])) {
      return null;
    }

    if (time() - $session['timestamp'] > SESSION_EXPIRATION) {
      return null;
    }

    return $session;
  }

  return null;
}
