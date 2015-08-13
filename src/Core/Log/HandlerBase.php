<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Psr\Log\LogLevel;

abstract class HandlerBase implements IHandler {
  /**
   * @var string
   */
  private $level = LogLevel::DEBUG;
  
  /**
   * Construct abstract log handler.
   * @param string $level Minimum log level, see {@see LogLevel}.
   */
  public function __construct($level = LogLevel::DEBUG) {
    $this->level = $level;
  }
  
  /**
   * {@inheritdoc}
   */
  public function accepts($level) {
    return Logger::compare($level, $this->level) >= 0;
  }
  
  /**
   * {@inheritdoc}
   */
  public function handleBatch(array $records) {
    foreach ($records as $record)
      $this->handle($record);
  }
  
  /**
   * Close handler.
   */
  public function close() {
  }
  
  public function __destruct() {
    $this->close();
  }
} 