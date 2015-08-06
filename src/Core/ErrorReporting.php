<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Utility class for handling errors/exceptions.
 */
class ErrorReporting {
  /**
   * @var callback \Exception handler.
   */
  private static $handler = null;

  /**
   * @var string What to do with PHP warnings, 'log' or 'exception'.
   */
  public static $warningBehavior = 'log';

  /**
   * @var string What to do with PHP notices, 'log' or 'exception'.
   */
  public static $noticeBehavior = 'log';

  /**
   * Private constructor.
   */
  private function __construct() {}

  /**
   * Handle PHP error.
   * @param int $type Type.
   * @param string $message Message.
   * @param string $file File.
   * @param int $line Line.
   * @throws ErrorException To convert PHP errors to exceptions.
   */
  public static function handleError($type, $message, $file, $line) {
    switch ($type) {
      case E_USER_ERROR:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
      case E_ERROR:
      case E_RECOVERABLE_ERROR:
        throw new ErrorException($message, 0, $type, $file, $line);
        break;
      case E_USER_WARNING:
      case E_USER_DEPRECATED:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
      case E_WARNING:
      case E_DEPRECATED:
      case E_PARSE:
      case E_NOTICE:
      case E_STRICT:
        if (self::$warningBehavior == 'log')
          Logger::log($message, Logger::WARNING, $file, $line);
        else if (self::$warningBehavior == 'exception')
          throw new ErrorException($message, 0, $type, $file, $line);
        break;
      case E_USER_NOTICE:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
        if (self::$noticeBehavior == 'log')
          Logger::log($message, Logger::NOTICE, $file, $line);
        else if (self::$noticeBehavior == 'exception')
          throw new ErrorException($message, 0, $type, $file, $line);
        break;
      default:
        throw new ErrorException($message, 0, $type, $file, $line);
        break;
    }
  }

  /**
   * Set exception handler.
   * @param callback $handler Function/method for handling exceptions.
   */
  public static function setHandler($handler) {
    self::$handler = $handler;
  }

  /**
   * Uncaught exception handler.
   * @param \Exception $exception \Exception.
   */
  public static function handleException(\Exception $exception) {
    if (isset(self::$handler)) {
      call_user_func(self::$handler, $exception); 
      return;
    }
    Logger::logException($exception);
    if (defined('OUTPUTTING')) {
      echo '<pre>';
    }
    else {
      header('Content-Type:text/plain');
    }
    $file = $exception->getFile();
    $line = $exception->getLine();
    $message = $exception->getMessage();
    echo 'Uncaught ' . get_class($exception) . ': ' . $message . PHP_EOL;

    echo tr(
      'An uncaught %1 was thrown in file %2 on line %3 that prevented further execution of this request.',
      get_class($exception), basename($file), $line);
    echo PHP_EOL;
    echo 'File: ' . $file . PHP_EOL;
    echo 'Stack trace:' . PHP_EOL;
    foreach ($exception->getTrace() as $i => $trace) {
      if (isset($trace['file'])) {
        echo $trace['file'] . ':';
        echo $trace['line'] . ' ';
      }
      if (isset($trace['class'])) {
        echo $trace['class'] . '::';
      }
      echo $trace['function'] . '(';
      $arglist = array();
      foreach ($trace['args'] as $arg) {
        $arglist[] = (is_scalar($arg) ? var_export($arg, true) : gettype($arg));
      }
      echo implode(', ', $arglist);
      echo ')' . PHP_EOL;
    }
    if (defined('OUTPUTTING')) {
      echo '</pre>';
    }
  }
}
