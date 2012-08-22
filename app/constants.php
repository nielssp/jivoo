<?php
/**
 * PeanutCMS constants
 *
 * @package PeanutCMS
 * @since 0.2.0
 */

/** PeanutCMS version string */
if (!defined('PEANUT_VERSION')) {
  define('PEANUT_VERSION', '0.3.4');
}

if (!defined('DEBUG')) {
  define('DEBUG', false);
}

if (!defined('LOG_ERRORS')) {
  define('LOG_ERRORS', false);
}

if (!defined('CACHING')) {
  define('CACHING', true);
}

if (!defined('HIDE_LEVEL')) {
  /**
   * How much to hide the identity of PeanutCMS
   * 0 : Reports version and name ("PeanutCMS " . PEANUT_VERSION)
   * 1 : Only reports name ("PeanutCMS")
   * 2 : Will (try to) hide everything
   * Level can be increased using the following configuration keys as well:
   *  - system.hide.identity (on/off)
   *  - system.hide.version (on/off)
   */
  define('HIDE_LEVEL', 0);
}

if (!defined('PREINSTALL_EXTENSIONS')) {
  define('PREINSTALL_EXTENSIONS', 'Tinymce Jquery JqueryUi JqueryHotkeys');
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

if (!defined('ALLOW_REDIRECT')) {
  define('ALLOW_REDIRECT', true);
}

if (!defined('META_MAX_LINES')) {
  /** How many lines to read before giving up looking for meta info in file (@see readFileMeta()) */
  define('META_MAX_LINES', 50);
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

if (!defined('CONTROLLERS')) {
  define('CONTROLLERS', APP . 'controllers/');
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

if (!defined('MODELS')) {
  define('MODELS', APP . 'models/');
}

if (!defined('SCHEMAS')) {
  define('SCHEMAS', APP . 'schemas/');
}

if (!defined('TEMPLATES')) {
  define('TEMPLATES', APP . 'templates/');
}

if (!defined('LANG')) {
  define('LANG', 'languages/');
}

if (!defined('EXTENSIONS')) {
  define('EXTENSIONS', 'extensions/');
}

if (!defined('THEMES')) {
  define('THEMES', 'themes/');
}

if (!defined('PUB')) {
  define('PUB', 'public/');
}

if (!defined('LOG')) {
  define('LOG', 'log/');
}

if (!defined('TMP')) {
  define('TMP', 'tmp/');
}


/** URL of current PeanutCMS installation */
if (!defined('PEANUT_URL')) {
  define('PEANUT_URL', 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH);
}
