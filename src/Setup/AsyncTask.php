<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

/**
 * An asynchronous installation task. Can be suspended and resumed later. The
 * actual computation happens in {@see run()}.
 */
interface AsyncTask {
  /**
   * Resume task.
   * @param array $state Saved state.
   */
  public function resume(array $state);
  
  /**
   * Suspend task.
   * @return array State data.
   */
  public function suspend();
  
  /**
   * Whether or not the task is done.
   * @return bool True if done.
   */
  public function isDone();
  
  /**
   * Get a status message.
   * @return string|null Status message if available.
   */
  public function getStatus();
  
  /**
   * Get progress percentage.
   * @return int|null Percentage if available.
   */
  public function getProgress();
  
  /**
   * Run task for a bit.
   */
  public function run();
}