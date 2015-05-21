<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

interface IAsyncTask {
  public function resume($state);
  public function suspend();
  public function isDone();
  public function getStatus();
  public function getProgress();
  public function run();
}