<?php
/**
 * PeanutCMS constants
 *
 * @package PeanutCMS
 * @since 0.2.0
 */

/** PeanutCMS version string */
if (!defined('PEANUT_VERSION')) {
  define('PEANUT_VERSION', '0.2.0');
}

if (!defined('DEBUG')) {
  define('DEBUG', FALSE);
}

/** The absolute path of this installation */
if (!defined('PATH')) {
  define('PATH', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/');
}

/** The path of this installation relative to website root */
if (!defined('WEBPATH')) {
  define(
    'WEBPATH',
    str_replace(
      rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'),
      '',
      PATH
    )
  );
}

/** Prefix for session-variables initialized by PeanutCMS */
if (!defined('SESSION_PREFIX')) {
  define('SESSION_PREFIX', 'peanut_');
}

if (!defined('PHP_VERSION_ID')) {
  $version = explode('.', PHP_VERSION);

  /** PHP version */
  define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

/** Default language */
if (!defined('LANGUAGE')) {
  define('LANGUAGE', 'en');
}

/** DIRECTORIES */

if (!defined('CFG')) {
  define('CFG', 'cfg/');
}

if (!defined('APP')) {
  define('APP', 'app/');
}

if (!defined('CLASSES')) {
  define('CLASSES', APP . 'classes/');
}

if (!defined('INTERFACES')) {
  define('INTERFACES', APP . 'interfaces/');
}

if (!defined('HELPERS')) {
  define('HELPERS', APP . 'helpers/');
}

if (!defined('MODULES')) {
  define('MODULES', APP . 'modules/');
}

if (!defined('TEMPLATES')) {
  define('TEMPLATES', APP . 'templates/');
}

if (!defined('THEMES')) {
  define('THEMES', 'themes/');
}

if (!defined('PUB')) {
  define('PUB', 'public/');
}



/** URL of current PeanutCMS installation */
if (!defined('PEANUT_URL')) {
  define('PEANUT_URL', 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH);
}