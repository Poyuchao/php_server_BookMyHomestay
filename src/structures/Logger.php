<?php

class Logger
{
  private static $instance = null;

  public static $LOG_LEVELS = [
    'VERBOSE' => -1,
    'DEBUG' => 0,
    'INFO' => 1,
    'ERROR' => 2
  ];

  public static $LOG_LEVEL = 0;

  private $stream;

  private $prefix = '';

  private function __construct(?string $prefix = null)
  {
    $this->prefix = $prefix ?? '';
    $this->stream = fopen(ROOT . 'data/logs.txt', 'a');
    if ($this->stream === false) {
      throw new Exception('Failed to open log file');
    }
  }

  public function __destruct()
  {
    fclose($this->stream);
  }

  private function log($level, $message)
  {
    $time = date('Y-m-d H:i:s');
    $message = print_r($message, true);
    $prefix = $this->prefix ? "[$this->prefix] " : '';
    fwrite($this->stream, "[$time] [$level] $prefix$message\n");
  }

  static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new Logger();
    }
    return self::$instance;
  }

  function verbose(mixed $message)
  {
    if (self::$LOG_LEVEL > self::$LOG_LEVELS['VERBOSE']) {
      return;
    }
    $this->log('VERBOSE', $message);
  }

  function debug(mixed $message)
  {
    if (self::$LOG_LEVEL > self::$LOG_LEVELS['DEBUG']) {
      return;
    }
    $this->log('DEBUG', $message);
  }

  function info(mixed $message)
  {
    if (self::$LOG_LEVEL > self::$LOG_LEVELS['INFO']) {
      return;
    }
    $this->log('INFO', $message);
  }

  function error(mixed $message)
  {
    $this->log('ERROR', $message);
  }

  static function globalDebug(mixed $message)
  {
    if (self::$LOG_LEVEL > self::$LOG_LEVELS['globalDEBUG']) {
      return;
    }
    self::getInstance()->log('globalDEBUG', $message);
  }

  static function globalInfo(mixed $message)
  {
    if (self::$LOG_LEVEL > self::$LOG_LEVELS['INFO']) {
      return;
    }
    self::getInstance()->log('INFO', $message);
  }

  static function globalError(mixed $message)
  {
    self::getInstance()->log('ERROR', $message);
  }

  static function setLogLevel($level)
  {
    self::$LOG_LEVEL = $level;
  }

  static function children($prefix)
  {
    return new Logger($prefix);
  }
}
