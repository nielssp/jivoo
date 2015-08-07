<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Jivoo\Core\Log\FileLogger;

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
   * Construct error handler.
   */
  public function __construct() {
    $this->logger = new FileLogger();
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
  }

  /**
   * Unregister error handler.
   */
  public function unregister() {
    restore_error_handler();
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
      case E_USER_WARNING:
      case E_USER_DEPRECATED:
      case E_WARNING:
      case E_DEPRECATED:
      case E_PARSE:
      case E_NOTICE:
      case E_STRICT:
        $this->logger->warning($message, array('file' => $file, 'line' => $line));
        break;
      case E_USER_NOTICE:
        $this->logger->notice($message, array('file' => $file, 'line' => $line));
        break;
      default:
        throw new ErrorException($message, 0, $type, $file, $line);
    }
  }
}