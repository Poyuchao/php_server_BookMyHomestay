<?php

require_once 'utils/stdout-log.php';
require_once 'utils/send-response.php';
require_once 'database/QueryBuilder.php';
require_once 'database/constants.php';

class Database
{
  private $host = "localhost";
  private $db_name ="homestay_database";
  private $db_username = "";
  private $db_password = "";
  private $connection;

  function __construct()
  {
    try {
      mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
      $this->connection = new mysqli('mysql', 'root', 'root', 'test_db');

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
