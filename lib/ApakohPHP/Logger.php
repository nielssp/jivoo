<?php
/**
 * ApakohPHP logger
 *
 * @package ApakohPHP
 */
class Logger {

  const NOTICE = 1;
  const WARNING = 2;
  const ERROR = 4;
  const ALL = 7;
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
        if ($this->level & $entry['type'] != 0) {
          fwrite($filePointer, tdate('c', $entry['time']));
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
    $message .= 'Stack trace:' . PHP_EOL;
    foreach ($exception->getTrace() as $i => $trace) {
      $message .= $trace['class'] . '::';
      $message .= $trace['function'] . ' in ';
      $message .= $trace['file'] . ' on line ' . $trace['line'] . PHP_EOL;
    }
    self::$log[] = array(
      'time' => time(),
      'message' => $message,
      'type' => Logger::ERROR
    );
  }
}
