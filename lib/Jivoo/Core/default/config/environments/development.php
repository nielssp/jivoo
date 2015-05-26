<?php
ini_set('display_errors', true);
return array(
  'core' => array(
    'showExceptions' => true,
    'logLevel' => Jivoo\Core\Logger::ALL,
    'createCrashReports' => false,
  ),
  'Extensions' => array(
    'disableBuggy' => false
  ),
  'Console' => array(
    'enable' => true
  ),
  'View' => array(
    'compileTemplates' => true
  )
);
