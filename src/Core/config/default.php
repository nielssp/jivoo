<?php
use Psr\Log\LogLevel;

switch ($environment) {
  case 'production':
    return array(
      'system' => array(
        'logLevel' => LogLevel::ERROR,
        'showExceptions' => false,
        'createCrashReports' => true,
      )
    );
  case 'development':
    return array(
      'system' => array(
        'logLevel' => LogLevel::DEBUG,
        'showExceptions' => true,
        'createCrashReports' => false,
      )
    );
  case 'cli':
    return array(
      'system' => array(
        'logLevel' => LogLevel::WARNING,
        'showExceptions' => true,
        'createCrashReports' => false,
      )
    );
  default:
    return array();
}