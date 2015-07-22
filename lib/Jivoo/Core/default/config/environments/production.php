<?php
return array(
  'core' => array(
    'showExceptions' => false,
    'logLevel' => Jivoo\Core\Logger::ERROR | Jivoo\Core\Logger::WARNING,
    'createCrashReports' => true,
  ),
  'Extensions' => array(
    'disableBuggy' => true
  ),
  'Console' => array(
    'enable' => false
  ),
  'View' => array(
    'compileTemplates' => false
  ),
  'Migrations' => array(
    'automigrate' => false
  )
);
