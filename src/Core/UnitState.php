<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * The possible states of a unit.
 */
class UnitState {
  /**
   * @var string Unit has not been loaded.
   */
  const UNLOADED = 'unloaded';
  
  /**
   * @var string Unit has been disabled (with the "cascade" option).
   */
  const DISABLED = 'disabled';
  
  /**
   * @var string Unit is enabled, but has not yet been executed.
   */
  const ENABLED = 'enabled';
  
  /**
   * @var string Unit has finished successfully.
   */
  const DONE = 'done';
  
  /**
   * @var string Unit failed during its execution.
   */
  const FAILED = 'failed';
}