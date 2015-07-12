<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Jivoo logging.
 */
class Logger {
  /**
   * @var int Database query log level.
   */
  const QUERY = 1;
  
  /**
   * @var int Debug log level.
   */
  const DEBUG = 2;
  
  /**
   * @var int Notice log level.
   */
  const NOTICE = 4;
  
  /**
   * @var int Warning log level.
   */
  const WARNING = 8;
  
  /**
   * @var int Error log level.
   */
  const ERROR = 16;
  
  /**
   * @var int All log levels.
   */
  const ALL = 31;
  
  /**
   * @var int No log levels.
   */
  const NONE = 0;

  /**
   * @var array[] List of log messages.
   */
  private static $log = array();

  /**
   * @var Logger[] List of active loggers.
   */
  private static $files = array();

  /**
   * @var string File path.
   */
  private $file;
  
  /**
   * @var int Log level bit mask.
   */
  private $level;
  
  /**
   * @var bool Whether to append messages to file.
   */
  private $append;

  /**
   * Constructor.
   * @param string $logFile Log file path.
   * @param int $level Log level bit mask.
   * @param string $append Whether to append messages to file.
   */
  private function __construct($logFile, $level = Logger::ALL, $append = true) {
    if (!file_exists($logFile)) {
      if (!touch($logFile)) {
        Logger::error(tr('Could not create log file: %1', $logFile));
      }
    }
    $this->file = realpath($logFile);
    $this->level = $level;
    $this->append = $append;
  }

  /**
   * Attempt to save the log.
   * @return boolean True if successful, false otherwise.
   */
  public function save() {
    if ($this->append) {
      if (!touch($this->file))
        return false;
    }
    $filePointer = fopen($this->file, $this->append ? 'a' : 'w');
    if ($filePointer) {
      foreach (self::$log as $entry) {
        if (($this->level & $entry['type']) != 0) {
          fwrite($filePointer, '[' . Logger::getType($entry['type']) . '] ');
          fwrite($filePointer, tdate('c', $entry['time']) . ':');
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
      return true;
    }
    return false;
  }

  /**
   * Get a string representation of a log level.
   * @param int $type Log level e.g. {@see Logger::NOTICE}.
   * @return string String representation e.g. 'NOTICE'.
   */
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

  /**
   * Get list of all log messages.
   * 
   * Each message is of the format:
   * <code>
   * array(
   *   'time' => ..., // Unix timestamp (int)
   *   'message' => ..., // Message (string)
   *   'type' => ..., // Message level (int)
   *   'file' => ..., // File path if applicable
   *   'line' => ... // Line if applicable
   * )
   * </code>
   * @return array[] List of log messages.
   */
  public static function getLog() {
    return self::$log;
  }
  
  /**
   * Save all attached log files.
   * @return boolean True if all files saved successfully, false otherwise.
   */
  public static function saveAll() {
    $status = true;
    foreach (self::$files as $file) {
      if (!$file->save())
        $status = false;
    }
    return $status;
  }

  /**
   * Attach a log file.
   * @param string $logFile Log file path.
   * @param int $level Log level bit mask, e.g. Logger::Notice | Logger::Error. 
   * @param string $append Whether or not to append messages to file.
   */
  public static function attachFile($logFile, $level = Logger::ALL, $append = true) {
    self::$files[] = new Logger($logFile, $level, $append);
  }

  /**
   * Log a message to the log.
   * @param string $message Log message.
   * @param int $type Log level e.g. Logger::WARNING.
   * @param string $file File if applicable.
   * @param int $line Line if applicable.
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
  
  /**
   * Log a database query.
   * @param string $message Log message.
   */
  public static function query($message) {
    self::log($message, Logger::QUERY);
  }

  /**
   * Log a debug message.
   * @param string $message Log message.
   */
  public static function debug($message) {
    self::log($message, Logger::DEBUG);
  }

  /**
   * Log a notice.
   * @param string $message Log message.
   */
  public static function notice($message) {
    self::log($message, Logger::NOTICE);
  }

  /**
   * Log a warning.
   * @param string $message Log message.
   */
  public static function warning($message) {
    self::log($message, Logger::WARNING);
  }

  /**
   * Log an error.
   * @param string $message Log message.
   */
  public static function error($message) {
    self::log($message, Logger::ERROR);
  }

  /**
   * Log an exception.
   * @param \Exception $exception \Exception.
   */
  public static function logException(\Exception $exception) {
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
    $previous = $exception->getPrevious();
    if (isset($previous)) {
      self::error(tr('The above exception was caused by:')); 
      self::logException($previous);
    }
  }
}
