<?php
/**
 * Main entry-script of Jivoo
 *
 * This file can be used to change the locations of files and directories
 * used by Jivoo. 
 *
 * @package Jivoo
 * @since 0.1.0
 */

require_once '../lib/Jivoo/Core/bootstrap.php';

$app = new App(include 'app/app.php', basename(__FILE__));

// Paths are relative to the current directory (dirname($_SERVER['SCRIPT_FILENAME']))
// unless they begin with '/' or 'x:' where x is any drive letter.
$app->paths->user = 'user';
$app->paths->log = 'user/log';
$app->paths->tmp = 'user/tmp';
$app->paths->themes = 'user/themes';

$app->run('development');
