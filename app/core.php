<?php
/**
 * PeanutCMS initialization
 *
 * @package PeanutCMS
 * @since 0.2.0
 */

if (!require_once('essentials.php')) {
  echo 'Essential PeanutCMS files are missing. You should probably reinstall.';
  exit;
}

// The autoloader has to be registered BEFORE session_start()
session_start();

if (PHP_VERSION_ID < 50200) {
  echo 'Sorry, but PeanutCMS does not support PHP versions below 5.2.0. ';
  echo 'You are currently using version ' . PHP_VERSION .'. ';
  echo 'You should contact your webhost. ';
  exit;
}

chdir(PATH);

// Modules that has to be initialized, the order does not matter
$modules = array(
  'errors', 'configuration',  'i18n', 'http', 'templates',
  'routes', 'theme', 'database', 'users', 'backend',
  'extensions', 'posts', 'links', 'pages'
);

Core::main($modules);

Hooks::run('modulesLoaded');

Hooks::run('preRender');

Hooks::run('render');

