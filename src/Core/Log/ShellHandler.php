<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Jivoo\Core\Cli\Shell;
use Psr\Log\LogLevel;

/**
 * CLI log handler.
 */
class ShellHandler extends HandlerBase {
  /**
   * @var Shell Shell.
   */
  protected $shell = null;
  
  /**
   * Construct CLI log handler.
   * @param Shell $shell Shell to log to.
   * @param string $level Minimum log level, see {@see \Psr\Log\LogLevel}.
   */
  public function __construct(Shell $shell, $level = LogLevel::INFO) {
    parent::__construct($level);
    $this->shell = $shell;
  }
  
  /**
   * {@inheritdoc}
   */
  public function handle(array $record) {
    $message = Logger::interpolate($record['message'], $record['context']);
      if ($record['level'] != LogLevel::INFO)
      $message = '[' . $record['level'] . '] ' . $message;
    if (Logger::compare($record['level'], LogLevel::ERROR) >= 0) {
      $this->shell->error($message);
    }
    else {
      $this->shell->put($message);
    }
  }
}