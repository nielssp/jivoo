<?php
class ErrorReporting {
  private function __construct() { }

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
        break;
      case E_USER_NOTICE:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
      case E_PARSE:
      case E_NOTICE:
      case E_STRICT:
        break;
      default:
        throw new ErrorException($message, 0, $type, $file, $line);
        break;
    }
  }

  /**
   * Uncaught Exception handler
   * @param Exception $exception Exception
   */
  public function handleException(Exception $exception) {
    if (defined('OUTPUTTING')) {
      echo 'Uncaught exception';
    }
    else {
      header('Content-Type:text/plain');
      $file = $exception->getFile();
      $line = $exception->getLine();
      $message = $exception->getMessage();
      /* This should (!!) be a template/view instead..
       * Or should it? (What if the template is missing?) */
      echo 'Uncaught ' . get_class($exception) . ': ' . $message . PHP_EOL;
  
      echo tr('An uncaught %1 was thrown in file %2 on line %3 that prevented further execution of this request.',
                    get_class($exception),
                    basename($file), $line);
      echo PHP_EOL;
      echo 'File: ' . $file . PHP_EOL;
      echo 'Stack trace:' . PHP_EOL;
      foreach ( $exception->getTrace() as $i => $trace ) {
        echo $trace['class'] . '::';
        echo $trace['function'] . ' in ';
        echo $trace['file'] . ' on line ' . $trace['line'] . PHP_EOL;
      }
    }
  }
}