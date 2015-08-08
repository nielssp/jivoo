<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Jivoo logging.
 * @deprecated Use {@see App::$logger}
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
   * @var LoggerInterface
   */
  private static $logger;


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
  
  public static function setLogger(LoggerInterface $logger) {
    self::$logger = $logger;
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
    if (self::$logger instanceof Log\Logger)
      return self::$logger->getLog();
    return array();
  }
  
  /**
   * Save all attached log files.
   * @return boolean True if all files saved successfully, false otherwise.
   */
  public static function saveAll() {
  }

  /**
   * Attach a log file.
   * @param string $logFile Log file path.
   * @param int $level Log level bit mask, e.g. Logger::Notice | Logger::Error. 
   * @param string $append Whether or not to append messages to file.
   */
  public static function attachFile($logFile, $level = Logger::ALL, $append = true) {
    if (self::$logger instanceof Log\Logger) {
      switch ($level) {
        case Logger::QUERY:
        case Logger::DEBUG:
        case Logger::ALL:
          $level = LogLevel::DEBUG;
          break;
        case Logger::NOTICE:
          $level = LogLevel::NOTICE;
          break;
        case Logger::ERROR:
          $level = LogLevel::ERROR;
          break;
        case Logger::WARNING:
        default:
          $level = LogLevel::WARNING;
          break;
      }
      return self::$logger->addHandler(new FileHandler($logFile, $level));
    }
  }

  /**
   * Log a message to the log.
   * @param string $message Log message.
   * @param int $type Log level e.g. Logger::WARNING.
   * @param string $file File if applicable.
   * @param int $line Line if applicable.
   */
  public static function log($message, $type = Logger::NOTICE, $file = null, $line = null) {
    $context = array();
    switch ($type) {
      case Logger::QUERY:
        $context['query'] = true;
      case Logger::DEBUG:
        $level = LogLevel::DEBUG;
        break;
      case Logger::NOTICE:
        $level = LogLevel::NOTICE;
        break;
      case Logger::ERROR:
        $level = LogLevel::ERROR;
        break;
      case Logger::WARNING:
      default:
        $level = LogLevel::WARNING;
        break;
    }
    if (isset($file))
      $context['file'] = $file;
    if (isset($file))
      $context['line'] = $line;
    if (isset(self::$logger))
      self::$logger->log($level, $message, $context);
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
    if (isset(self::$logger))
      self::$logger->error($exception->getMessage(), array('exception' => $exception));
  }
}
