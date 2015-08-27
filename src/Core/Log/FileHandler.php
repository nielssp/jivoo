<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Jivoo\Core\Utilities;
use Psr\Log\LogLevel;

/**
 * File log handler.
 */
class FileHandler extends StreamHandler {
  /**
   * @var string
   */
  private $file;
  
  /**
   * Construct file log handler.
   * @param string $filePath Log file path.
   * @param string $level Minimum log level, see {@see \Psr\Log\LogLevel}.
   * @param bool $useLocking Whether to lock the file before appending to ensure
   * atomicity of each write.
   */
  public function __construct($filePath, $level = LogLevel::DEBUG, $useLocking = false) {
    if (!file_exists($filePath)) {
      $dir = dirname($filePath);
      if (!Utilities::dirExists($dir)) {
        trigger_error(tr('Could not create log directory: %1', $dir), E_USER_WARNING);
        $this->stream = false;
        return;
      }
      if (!touch($filePath)) {
        trigger_error(tr('Could not create log file: %1', $filePath), E_USER_WARNING);
        $this->stream = false;
        return;
      }
    }
    $this->file = realpath($filePath);
    parent::__construct(null, $level, $useLocking);
  }
  
  /**
   * {@inheritdoc}
   */
  public function handle(array $record) {
    if ($this->stream === false)
      return;
    if (!isset($this->stream)) {
      $this->stream = fopen($this->file, 'a');
      if (!$this->stream) {
        $this->stream = false;
        trigger_error(tr('Could not open file: %1', $this->file), E_USER_WARNING);
        return;
      }
    }
    parent::handle($record);
  }
}