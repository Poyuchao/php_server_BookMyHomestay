<?php

require_once ROOT . 'utils/stdout-log.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'database/constants.php';

class Database
{
  public mysqli $connection;

  function __construct()
  {
    try {
      mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
      $this->connection = new mysqli(DB_SERVER_NAME, DB_USER_NAME, DB_PASSWORD, DB_NAME);
      
      if ($this->connection->connect_error) {
        stdoutLog('Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
        send_error_response('Database connection error', 500);
      }

      $this->connection->set_charset('utf8mb4');
    } catch (Exception $e) {
      stdoutLog('Exception: ' . $e->getMessage());
      send_error_response('Database connection error', 500);
    }
  }

  function __destruct()
  {
    $this->connection->close();
  }
}
