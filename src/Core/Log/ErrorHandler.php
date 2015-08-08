<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * A handler for PHP errors and errors triggered using {@see trigger_error}.
 */
class ErrorHandler implements LoggerAwareInterface {
  /**
   * @var ErrorHandler
   */
  private static $instance = null;
  
  /**
   * @var LoggerInterface
   */
  private $logger;
  
  /**
   * @var string[]
   */
  private $map = array(
    E_ERROR => LogLevel::CRITICAL,
    E_WARNING => LogLevel::WARNING,
    E_PARSE => LogLevel::ALERT,
    E_NOTICE => LogLevel::WARNING,
    E_CORE_ERROR => LogLevel::CRITICAL,
    E_CORE_WARNING => LogLevel::WARNING,
    E_COMPILE_ERROR => LogLevel::ALERT,
    E_COMPILE_WARNING => LogLevel::WARNING,
    E_USER_ERROR => LogLevel::ERROR,
    E_USER_WARNING => LogLevel::WARNING,
    E_USER_NOTICE => LogLevel::NOTICE,
    E_STRICT => LogLevel::WARNING,
    E_RECOVERABLE_ERROR => LogLevel::ERROR,
    E_DEPRECATED => LogLevel::WARNING,
    E_USER_DEPRECATED => LogLevel::WARNING
  );
  
  /**
   * @var string[]
   */
  private static $strings = array(
    E_ERROR => 'E_ERROR',
    E_WARNING => 'E_WARNING',
    E_PARSE => 'E_PARSE',
    E_NOTICE => 'E_NOTICE',
    E_CORE_ERROR => 'E_CORE_ERROR',
    E_CORE_WARNING => 'E_CORE_WARNING',
    E_COMPILE_ERROR => 'E_COMPILE_ERROR',
    E_COMPILE_WARNING => 'E_COMPILE_WARNING',
    E_USER_ERROR => 'E_USER_ERROR',
    E_USER_WARNING => 'E_USER_WARNING',
    E_USER_NOTICE => 'E_USER_NOTICE',
    E_STRICT => 'E_STRICT',
    E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
    E_DEPRECATED => 'E_DEPRECATED',
    E_USER_DEPRECATED => 'E_USER_DEPRECATED'
  );
  
  /**
   * Construct error handler.
   */
  public function __construct($map = null) {
    $this->logger = new Logger();
    if (isset($map)) {
      foreach ($map as $code => $level)
        $this->map[$code] = $level;
    }
  }
  
  /**
   * Get singleton error handler instance.
   * @return ErrorHandler Error handler.
  */
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new self();
    return self::$instance;
  }
  
  /**
   * Register error handler.
   */
  public function register() {
    set_error_handler(array($this, 'handle'));
    register_shutdown_function(array($this, 'handleFatal'));
  }

  /**
   * Unregister error handler.
   */
  public function unregister() {
    restore_error_handler();
  }
  
  /**
   * Map a PHP error code to a log level.
   * @param int $errorCode Error code.
   * @param string $logLevel Log level, see {@see LogLevel}.
   */
  public function map($errorCode, $logLevel) {
    $this->map[$errorCode] = $logLevel;
  }

  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }
  
  /**
   * Get logger.
   * @return LoggerInterface Logger.
   */
  public function getLogger() {
    return $this->logger;
  }
  
  /**
   * Convert PHP error code to string.
   * @param int $type Error code.
   * @return string Error code as a string.
   */
  public static function toString($type) {
    if (!isset(self::$strings[$type]))
      return $type;
    return self::$strings[$type];
  }
  
  /**
   * Handle error.
   * @param int $type Error type.
   * @param string $message Error message.
   * @param string $file File.
   * @param int $line Line.
   * @throws ErrorException To convert errors (E_USER_ERROR, E_ERROR, and
   * E_RECOVERABLE_ERROR) to exceptions.
   */
  public function handle($type, $message, $file, $line) {
    switch ($type) {
      case E_USER_NOTICE:
      case E_USER_DEPRECATED:
      case E_USER_WARNING:
      case E_USER_ERROR:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
        break;
    }
    switch ($type) {
      case E_USER_ERROR:
      case E_ERROR:
      case E_RECOVERABLE_ERROR:
        throw new ErrorException($message, 0, $type, $file, $line);
      default:
        $this->logger->log(
          $this->map[$type],
          self::$strings[$type] . ': ' . $message,
          array('file' => $file, 'line' => $line, 'code' => $type)
        );
    }
  }
  
  /**
   * Handle a fatal error.
   */
  public function handleFatal() {
    $error = error_get_last();
    if ($error) {
      switch ($error['type']) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
          $this->logger->log(
            $this->map[$error['type']],
            'Fatal error (' . self::$strings[$error['type']] . '): ' . $error['message'],
            array(
              'file' => $error['file'],
              'line' => $error['line'],
              'code' => $error['type']
            )
          );
      }
    }
  }
  
  /**
   * Catch a PHP error or warning message.
   * @param callable $callable Function to catch error in.
   * @return string|null Error message or null if no error was triggered.
   */
  public static function catchError($callable) {
    $error = null;
    set_error_handler(function($type, $message) use($error) {
      $error = $message;
    });
    $callable();
    restore_error_handler();
    return $error;
  }
}