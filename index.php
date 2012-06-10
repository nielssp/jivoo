<?php
/**
 * Main configuration file and entry-script of PeanutCMS
 *
 * This file can be used to change PeanutCMS constants.
 * (Available constants can be found in app/constants.php)
 *
 * @package PeanutCMS
 * @since 0.1.0
 */

define('APP', 'app/');

//define('PUB', '../../public/');

define('DEBUG', TRUE);

define('LOG_ERRORS', TRUE);

require_once(APP . 'core.php');

