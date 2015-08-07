<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Default Jivoo logger that can log to multiple files.
 */
class FileLogger implements LoggerInterface {
  /**
   * @var array[]
   */
  private $log = array();

  /**
   * @var string[][]
   */
  private $files = array(
    'emergency' => array(),
    'alert' => array(),
    'critical' => array(),
    'error' => array(),
    'warning' => array(),
    'notice' => array(),
    'info' => array(),
    'debug' => array()
  );
  
  /**
   * @var bool
   */
  private $useLocking = false;
  
  /**
   * Get all logged messages.
   */
  public function getLog() {
    return $this->log;
  }
  
  /**
   * Add a log file to the logger.
   * @param string $filePath Log file path.
   * @param string $minLevel Minimum log level, see {@see LogLevel}.
   * @throws Jivoo\Core\LogException If log level is undefined.
   */
  public function addFile($filePath, $minLevel = LogLevel::WARNING) {
    if (!file_exists($filePath)) {
      if (!touch($filePath)) {
        $this->error(tr('Could not create log file: %1', $filePath));
      }
    }
    $filePath = realpath($filePath);
    switch ($minLevel) {
      case LogLevel::DEBUG:
        $this->files[LogLevel::DEBUG][] = $filePath;
      case LogLevel::INFO:
        $this->files[LogLevel::INFO][] = $filePath;
      case LogLevel::NOTICE:
        $this->files[LogLevel::NOTICE][] = $filePath;
      case LogLevel::WARNING:
        $this->files[LogLevel::WARNING][] = $filePath;
      case LogLevel::ERROR:
        $this->files[LogLevel::ERROR][] = $filePath;
      case LogLevel::CRITICAL:
        $this->files[LogLevel::CRITICAL][] = $filePath;
      case LogLevel::ALERT:
        $this->files[LogLevel::ALERT][] = $filePath;
      case LogLevel::EMERGENCY:
        $this->files[LogLevel::EMERGENCY][] = $filePath;
        break;
      default:
        throw new LogException(tr('Undefined log level: %1', $minLevel));
    }
  }
  
  /**
   * Attempt to save log files.
   */
  public function save() {
    $logs = array();
    foreach ($this->log as $record) {
      if (count($this->files[$record['level']])) {
        $formatted = self::format($record);
        foreach ($this->files[$record['level']] as $file) {
          if (!isset($logs[$file]))
            $logs[$file] = '';
          $logs[$file] .= $formatted;
        }
      }
    }
    foreach ($logs as $file => $messages) {
      $f = fopen($file, 'a');
      if (!$f) {
        // TODO: log this error... how?
        continue;
      }
      if ($this->useLocking)
        flock($f, LOCK_EX);
      fwrite($f, $messages);
      if ($this->useLocking)
        flock($f, LOCK_UN);
      fclose($f);
    }
  }
  
  /**
   * Format a log message for a log file.
   * @param array $record Log message array.
   * @return string Formatted log message followed by a line break.
   */
  public static function format(array $record) {
    $seconds = (int) $record['time'];
    $millis = floor(($record['time'] - $seconds) * 1000);
    $timestamp = date('Y-m-d H:i:s', $seconds);
    $timestamp .= sprintf('.%03d ', $millis);
    $timestamp .= date('P');
    $level = '[' . $record['level'] . ']';
    $message = '';
    if (isset($record['context']['query']))
      $message .= '(query) ';
    $message .= self::interpolate($record['message'], $record['context']);
    if (isset($record['context']['file']))
      $message .= ' in ' . $record['context']['file'];
    if (isset($record['context']['line']))
      $message .= ' on line ' . $record['context']['line'];
    return $timestamp . ' ' . $level .  ' ' . $message . PHP_EOL;
  }
  
  /**
   * Interpolate context values into message.
   * @param string $message Message string.
   * @param array $context Context values.
   * @return string Interpolated message string.
   */
  public static function interpolate($message, array $context = array()) {
    foreach ($context as $key => $value)
      str_replace('{' . $key . '}', $value, $message);
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
    if (!isset($this->files[$level]))
      throw new LogException(tr('Undefined log level: %1', $level));
    $this->log[] = array(
      'level' => $level,
      'message' => $message,
      'context' => $context,
      'time' => microtime(true)
    );
  }
}