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

require PATH . '/functions.php';
require PATH . '/exceptions.php';
require PATH . '/Autoloader.php';

Autoloader::getInstance()->register();
Autoloader::getInstance()->addPath('Jivoo\\', PATH);

error_reporting(-1);
set_error_handler(array('Jivoo\Core\ErrorReporting', 'handleError'));