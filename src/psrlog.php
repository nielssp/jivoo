<?php
// PSR-3 Logger interfaces used if the "psr/log" package hasn't been loaded at
// the time of including "bootstrap.php".
// See http://www.php-fig.org/psr/psr-3/
// and https://github.com/php-fig/log
namespace Psr\Log;

class InvalidArgumentException extends \InvalidArgumentException {}

class LogLevel {
  const EMERGENCY = 'emergency';
  const ALERT = 'alert';
  const CRITICAL = 'critical';
  const ERROR = 'error';
  const WARNING = 'warning';
  const NOTICE = 'notice';
  const INFO = 'info';
  const DEBUG = 'debug';
}

interface LoggerAwareInterface {
  public function setLogger(LoggerInterface $logger);
}

interface LoggerInterface {
  public function emergency($message, array $context = array());
  public function alert($message, array $context = array());
  public function critical($message, array $context = array());
  public function error($message, array $context = array());
  public function warning($message, array $context = array());
  public function notice($message, array $context = array());
  public function info($message, array $context = array());
  public function debug($message, array $context = array());
  public function log($level, $message, array $context = array());
}