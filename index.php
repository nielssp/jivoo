<?php
/**
 * Main configuration file and entry-script of PeanutCMS
 *
 * You can manually edit constants defined in this file or do it from the
 * PeanutCMS backend.
 *
 * @package PeanutCMS
 * @since 0.1.0
 */

/** The absolute path of this installation */
define('PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');

/** Directory which contains the PeanutCMS files (relative to PATH) */
define('INC', 'includes/');

require_once(PATH . INC . 'core.php');

