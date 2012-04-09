<?php
/**
 * PeanutCMS initialization
 *
 * @package PeanutCMS
 * @since 0.2.0
 */

// To hell with those "magic quotes"!
ini_set('magic_quotes_runtime', 0);

session_start();

if (!require_once('essentials.php')) {
  echo 'Essential PeanutCMS files are missing. You should probably reinstall.';
  exit;
}

if (PHP_VERSION_ID < 50200) {
  echo 'Sorry, but PeanutCMS does not support PHP versions below 5.2.0. You are currently using version ' . PHP_VERSION .'.';
  echo 'You should contact your webhost.';
  exit;
}

chdir(PATH);

// Classes that has to be initialized, the order does not matter
$modules = array(
  'errors', 'configuration',  'i18n', 'http', 'templates',
  'actions', 'routes', 'theme', 'database', 'posts'
);

$core = new Core(p(CFG . 'blacklist'));

foreach ($modules as $module) {
  try {
    $core->loadModule($module);
  }
  catch (ModuleBlacklistedException $e) {
    // The user has blacklisted this module, continue loading other modules
    continue;
  }
  catch (ModuleNotFoundException $e) {
    if (class_exists('Errors')) {
      Errors::fatal(
      	tr('Module not found'),
        $e->getMessage(),
        /** @todo Add useful information, might even be an idea to automatically fix the problem (depending on module) */
        '<p>!!Information about how to fix this problem (as a webmaster) here!!</p>'
         . '<h2>Solution 1: Blacklist "' . $module . '" module</h2>'
         . '<p>Open the file ' . w(CFG . 'blacklist') . ' and add "' . $module . '" to a '
         . 'new line. This will prevent PeanutCMS from attempting to load the module.</p>'
         . '<h2>Solution 2: Reinstall "' . $module . '"</h2>'
      );
    }
  }
}

Hooks::run('preRender');

Hooks::run('render');

