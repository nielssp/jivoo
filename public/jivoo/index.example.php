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

$appName = 'jivoo';

require_once '../../lib/Jivoo/Core/bootstrap.php';

$app = new App(include '../../app/' . $appName . '/app.php', basename(__FILE__));

$userDir = '../../user/' . $appName;

// Paths are relative to the current directory (dirname($_SERVER['SCRIPT_FILENAME']))
// unless they begin with '/' or 'x:' where x is any drive letter.
$app->paths->user = $userDir;
$app->paths->log = $userDir . '/log';
$app->paths->tmp = $userDir . '/tmp';
$app->paths->extensions = $userDir . '/extensions';
$app->paths->themes = $userDir . '/themes';
$app->paths->media = $userDir . '/media';

$app->run('development');
