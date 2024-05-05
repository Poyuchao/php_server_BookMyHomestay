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
      return send_error_response('Unauthorized', 401);
    }

    if (!isset($session['timestamp'])) {
      return send_error_response('User session expired', 401);
    }

    if (time() - $session['timestamp'] > SESSION_EXPIRATION) {
      return send_error_response('User session expired', 401);
    }

    return $session;
  }

  return null;
}
