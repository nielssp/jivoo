<?php
class ErrorReporting {

  private static $handler = null;

  private function __construct() {}

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
        Logger::log($message, Logger::WARNING, $file, $line);
        break;
      case E_USER_NOTICE:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
      case E_PARSE:
      case E_NOTICE:
      case E_STRICT:
        Logger::log($message, Logger::NOTICE, $file, $line);
        break;
      default:
        throw new ErrorException($message, 0, $type, $file, $line);
        break;
    }
  }

  public static function setHandler($handler) {
    self::$handler = $handler;
  }

  /**
   * Uncaught Exception handler
   * @param Exception $exception Exception
   */
  public static function handleException(Exception $exception) {
    Logger::logException($exception);
    if (isset(self::$handler)) {
      call_user_func(self::$handler, $exception); 
      return;
    }
    if (defined('OUTPUTTING')) {
      echo 'Uncaught exception';
    }
    else {
      header('Content-Type:text/plain');
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
        echo $trace['file'] . ':';
        echo $trace['line'] . ' ';
        echo $trace['class'] . '::';
        echo $trace['function'] . '(';
        $arglist = array();
        foreach ($trace['args'] as $arg) {
          $arglist[] = (is_scalar($arg) ? $arg : gettype($arg));
        }
        echo implode(', ', $arglist);
        echo ')' . PHP_EOL;
      }
    }
  }
}
