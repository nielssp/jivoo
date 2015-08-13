<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

/**
 * Implements progress and status updates for asynchronous tasks.
 */
abstract class AsyncTaskBase implements AsyncTask {
  /**
   * @var string Status message;
   */
  private $status = null;
  
  /**
   * @var int Progress.
   */
  private $progress = null;

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    $status = $this->status;
    $this->status = null;
    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function getProgress() {
    $progress = $this->progress;
    $this->progress = null;
    return $progress;
  }

  /**
   * Set status.
   * @param string $status Status message.
   */
  protected function status($status) {
    $this->status = $status;
  }

  /**
   * Set progress.
   * @param int $progress Percentage.
   */
  protected function progress($progress) {
    $this->progress = $progress;
  }
}