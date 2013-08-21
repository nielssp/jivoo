<?php
/**
 * ApakohPHP logger
 *
 * @package ApakohPHP
 */
class Logger {

  const QUERY = 1;
  const DEBUG = 2;
  const NOTICE = 4;
  const WARNING = 8;
  const ERROR = 16;
  const ALL = 31;
  const NONE = 0;

  private static $log = array();

  private static $files = array();

  private $file;
  private $level;
  private $append;

  private function __construct($logFile, $level = Logger::ALL, $append = true) {
    $this->file = $logFile;
    $this->level = $level;
    $this->append = $append;
  }

  function __destruct() {
    $filePointer = fopen($this->file, $this->append ? 'a' : 'w');
    if ($filePointer) {
      foreach (self::$log as $entry) {
        if (($this->level & $entry['type']) != 0) {
          fwrite($filePointer, tdate('c', $entry['time']));
          fwrite($filePointer, ' ' . Logger::getType($entry['type']) . ':');
          fwrite($filePointer, ' ' . $entry['message']);
          if (isset($entry['file'])) {
            fwrite($filePointer, ' in ' . $entry['file']);
          }
          if (isset($entry['line'])) {
            fwrite($filePointer, ' on line ' . $entry['line']);
          }
          fwrite($filePointer, PHP_EOL);
        }
      }
      fclose($filePointer);
    }
  }

  public static function getType($type) {
    switch ($type) {
      case Logger::QUERY:
        return 'QUERY';
      case Logger::DEBUG:
        return 'DEBUG';
      case Logger::NOTICE:
        return 'NOTICE';
      case Logger::WARNING:
        return 'WARNING';
      case Logger::ERROR:
        return 'ERROR';
    }
    return 'UNKNOWN';
  }

  public static function getLog() {
    return self::$log;
  }

  public static function attachFile($logFile, $level = Logger::ALL, $append = true) {
    self::$files[] = new Logger($logFile, $level, $append);
  }

  /**
   * Log a message to the log
   * @param string $message Log message
   */
  public static function log($message, $type = Logger::NOTICE, $file = null, $line = null) {
    $entry = array(
      'time' => time(),
      'message' => $message,
      'type' => $type,
      'file' => $file,
      'line' => $line
    );
    self::$log[] = $entry;
  }
  
  public static function query($message) {
    self::log($message, Logger::QUERY);
  }
  
  public static function debug($message) {
    self::log($message, Logger::DEBUG);
  }
  
  public static function notice($message) {
    self::log($message, Logger::NOTICE);
  }
  
  public static function warning($message) {
    self::log($message, Logger::WARNING);
  }
  
  public static function error($message) {
    self::log($message, Logger::ERROR);
  }

  public static function logException(Exception $exception) {
    $file = $exception->getFile();
    $line = $exception->getLine();
    $message = tr(
      'An uncaught %1 was thrown in file %2 on line %3 and caused execution to be terminated.',
      get_class($exception),
      basename($file),
      $line
    ) . PHP_EOL;
    $message .= 'Message: ' . $exception->getMessage() . PHP_EOL;
    $message .= 'File: ' . $file . PHP_EOL;
    $message .= 'Line: ' . $line . PHP_EOL;
    $message .= 'Stack trace:' . PHP_EOL;
    foreach ($exception->getTrace() as $i => $trace) {
      if (isset($trace['file'])) {
        $message .=  $trace['file'] . ':';
        $message .=  $trace['line'] . ' ';
      }
      if (isset($trace['class'])) {
        $message .=  $trace['class'] . '::';
      }
      $message .=  $trace['function'] . '(';
      $arglist = array();
      foreach ($trace['args'] as $arg) {
        $arglist[] = (is_scalar($arg) ? var_export($arg, true) : gettype($arg));
      }
      $message .=  implode(', ', $arglist);
      $message .=  ')' . PHP_EOL;
    }
    self::$log[] = array(
      'time' => time(),
      'message' => $message,
      'type' => Logger::ERROR,
      'file' => null,
      'line' => null
    );
  }
}
