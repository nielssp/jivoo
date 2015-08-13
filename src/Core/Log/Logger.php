<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Default Jivoo logger that can log to one or more log handlers. It also keeps
 * track of all log messages.
 */
class Logger implements LoggerInterface {
  /**
   * @var array[]
   */
  private $log = array();

  /**
   * @var Handler[][]
   */
  private $handlers = array(
    LogLevel::EMERGENCY => array(),
    LogLevel::ALERT => array(),
    LogLevel::CRITICAL => array(),
    LogLevel::ERROR => array(),
    LogLevel::WARNING => array(),
    LogLevel::NOTICE => array(),
    LogLevel::INFO => array(),
    LogLevel::DEBUG => array()
  );
  
  /**
   * @var int[]
   */
  private static $ordering = array(
    LogLevel::EMERGENCY => 70,
    LogLevel::ALERT => 60,
    LogLevel::CRITICAL => 50,
    LogLevel::ERROR => 40,
    LogLevel::WARNING => 30,
    LogLevel::NOTICE => 20,
    LogLevel::INFO => 10,
    LogLevel::DEBUG => 0
  );
  
  /**
   * Get all logged messages.
   * @return array[] Array of logged records.
   */
  public function getLog() {
    return $this->log;
  }
  
  /**
   * Add a log handler.
   * @param Handler $handler Log handler.
   * @param bool $getsPrevious Whether this log handler should get a batch of
   * all previously logged messages.
   * @param bool $prepend If true, the handler is prepended instead of appended.
   */
  public function addHandler(Handler $handler, $getsPrevious = true, $prepend = false) {
    foreach ($this->handlers as $level => $handlers) {
      if ($handler->accepts($level)) {
        if ($prepend)
          array_unshift($this->handlers[$level], $handler);
        else
          array_push($this->handlers[$level], $handler);
      }
    }
    if ($getsPrevious) {
      $records = array();
      foreach ($this->log as $record) {
        if ($handler->accepts($record['level']))
          $records[] = $record;
      }
      if (count($records))
        $handler->handleBatch($records);
    }
  }
  
  /**
   * Compare two log levels.
   * @param string $levelA First log level, see {@see LogLevel}.
   * @param string $levelB Second log level.
   * @return int Returns a <0 if $levelA is lower than $levelB, >0 if $levelA
   * is greater than $levelB, and 0 if $levelA is equal to $levelB.
   */
  public static function compare($levelA, $levelB) {
    return self::$ordering[$levelA] - self::$ordering[$levelB];
  }
  
  /**
   * Interpolate context values into message.
   * @param string $message Message string.
   * @param array $context Context values.
   * @return string Interpolated message string.
   */
  public static function interpolate($message, array $context = array()) {
    foreach ($context as $key => $value)
      $message = str_replace('{' . $key . '}', $value, $message);
    return $message;
  }
  
  /**
   * {@inheritdoc}
   */
  public function emergency($message, array $context = array()) {
    $this->log(LogLevel::EMERGENCY, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function alert($message, array $context = array()) {
    $this->log(LogLevel::ALERT, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function critical($message, array $context = array()) {
    $this->log(LogLevel::CRITICAL, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function error($message, array $context = array()) {
    $this->log(LogLevel::ERROR, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function warning($message, array $context = array()) {
    $this->log(LogLevel::WARNING, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function notice($message, array $context = array()) {
    $this->log(LogLevel::NOTICE, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function info($message, array $context = array()) {
    $this->log(LogLevel::INFO, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function debug($message, array $context = array()) {
    $this->log(LogLevel::DEBUG, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
    if (!isset($this->handlers[$level]))
      throw new LogException(tr('Undefined log level: %1', $level));
    $record = array(
      'level' => $level,
      'message' => (string) $message,
      'context' => $context,
      'time' => microtime(true) 
    );
    
    $this->log[] = $record;
    
    if (count($this->handlers[$level])) {
      foreach ($this->handlers[$level] as $handler)
        $handler->handle($record);
    }
  }
}