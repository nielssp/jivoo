<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * A task that runs periodically.
 */
interface ITask {
  /**
   * Delay between runs in seconds.
   * @return int Number of seconds that must pass before the next run.
   */
  public function getDelay();
  
  /**
   * Run the task.
   */
  public function run();
}