<?php
return array(
  'core' => array(
    'showExceptions' => true,
    'logLevel' => 31,
    'language' => 'en',
    'timeZone' => 'UTC',
    'createCrashReports' => false,
  ),
  'Routing' => array(
    'rewrite' => false,
    'index' => array(
      'path' => 'posts',
      'query' => array(
      ),
    ),
    'sessionPrefix' => 'acblog_',
  ),
  'Assets' => array(
  ),
  'Models' => array(
  ),
  'Helpers' => array(
  ),
  'Templates' => array(
    'title' => 'ACBlog',
    'subtitle' => 'Powered by ACBlog',
  ),
  'Controllers' => array(
  ),
  'Setup' => array(
  ),
  'Editors' => array(
  ),
  'Shadow' => array(
    'hashType' => 'sha512',
  ),
  'Database' => array(
    'server' => 'localhost',
    'database' => 'acblog',
    'filename' => '/home/www/GOTUN/PeanutCMS/tutorial/acblog/app/config/db.sqlite3',
    'driver' => 'Sqlite3',
    'configured' => true,
    'tablePrefix' => '',
    'migration' => array(
      'users' => '0.0.1',
      'usersessions' => '0.0.1',
      'groups' => '0.0.1',
      'groups_permissions' => '0.0.1',
      'posts' => '0.0.1',
    ),
  ),
  'Authentication' => array(
    'defaultGroups' => array(
      'unregistered' => 'guests',
      'registered' => 'users',
    ),
    'rootCreated' => true,
    'sessionLifetime' => 1800,
    'longSessionLifetime' => 1209600,
    'renewSessionAfter' => 300,
  ),
  'Toolkit' => array(
  ),
  'Core' => array(
  ),
);
