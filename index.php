<?php
/**
 * Main configuration file and entry-script of PeanutCMS
 *
 * This file can be used to change PeanutCMS constants.
 * (Available constants can be found in includes/core.php)
 *
 * @package PeanutCMS
 * @since 0.1.0
 */

/** The absolute path of this installation */
define('PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');

/** Directory which contains the PeanutCMS files (relative to PATH) */
define('INC', 'includes/');

define('DEBUG', true);

require_once(PATH . INC . 'core.php');

