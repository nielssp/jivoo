<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

abstract class AsyncTask implements IAsyncTask {
  private $status = null;
  private $progress = null;

  public function getStatus() {
    $status = $this->status;
    $this->status = null;
    return $status;
  }

  public function getProgress() {
    $progress = $this->progress;
    $this->progress = null;
    return $progress;
  }

  protected function status($status) {
    $this->status = $status;
  }

  protected function progress($progress) {
    $this->progress = $progress;
  }
}