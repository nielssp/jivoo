<?php

if (!defined('LIB_PATH')) {
  define('LIB_PATH', str_replace('\\', '/', dirname(__FILE__)));
}

require LIB_PATH . '/Lib.php';

function __autoload($class) {
  Lib::autoload($class);
}

spl_autoload_register('__autoload');

Lib::import('*');
