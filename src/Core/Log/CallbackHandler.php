<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Jivoo\Core\Utilities;
use Psr\Log\LogLevel;

/**
 * Callback log handler.
 */
class CallbackHandler extends HandlerBase {
  /**
   * @var callback Callback.
   */
  protected $callback = null;
  
  /**
   * Construct callback log handler.
   * @param callabke $callback Log hanlder function.
   * @param string $level Minimum log level, see {@see \Psr\Log\LogLevel}.
   */
  public function __construct($callback, $level = LogLevel::DEBUG) {
    parent::__construct($level);
    $this->callback = $callback;
  }
  
  /**
   * {@inheritdoc}
   */
  public function handle(array $record) {
    call_user_func($this->callback, $record);
  }
  
}