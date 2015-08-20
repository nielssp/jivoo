<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\Core\Store\Document;

/**
 * An initialization unit.
 */
interface Unit {
  /**
   * Run unit.
   */
  public function run(App $app, Document $config);
  
  /**
   * Get dependencies of unit, i.e. the names of the units that must run before
   * this one.
   * @return string[] Unit class names.
   */
  public function requires();
  
  /**
   * Get optional dependencies of unit, i.e. the names of the units that, if
   * they are loaded, must run before this one.
   * @return string[] Unit class names.
   */
  public function after();

  /**
   * Get optional dependencies of unit, i.e. the names of the units that, if
   * they are loaded, must run after this one.
   * @return string[] Unit class names.
   */
  public function before();
}