<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo;

if (defined('Jivoo\VERSION'))
  return;

/**
 * @var string Jivoo framework version string.
 */
const VERSION = '0.21.0-dev';

/**
 * @var string Absolute path to Jivoo source directory.
 */
define('Jivoo\PATH', str_replace('\\', '/', dirname(__FILE__)));

require PATH . '/Autoloader.php';

if (version_compare(phpversion(), '5.3.0') < 0) {
  echo 'The Jivoo web application framework does not support PHP ' . phpversion()
    . '. PHP 5.3.0 or above is required.';
  exit(1);
}

Autoloader::getInstance()->register();
Autoloader::getInstance()->addPath('Jivoo\\', PATH);

if (!interface_exists('Psr\Log\LoggerInterface')) {
  require PATH . '/psrlog.php';
}

require PATH . '/functions.php';
require PATH . '/exceptions.php';

error_reporting(-1);
set_error_handler(array('Jivoo\Core\ErrorReporting', 'handleError'));

