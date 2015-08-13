<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

/**
 * A log handler.
 */
interface IHandler {
  /**
   * Whether this handler accepts log records at this level.
   * @param string $level Log level, see {@see \Psr\Log\LogLevel}.
   * @return bool True if handler accepts the log level.
   */
  public function accepts($level);
  
  /**
   * Handle a single log record.
   * @param array $record Log record to handle.
   */
  public function handle(array $record);
  
  /**
   * Handle multiple log records.
   * @param array[] $record Array of records.
   */
  public function handleBatch(array $records);
}