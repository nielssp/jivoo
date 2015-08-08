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
class FileHandler extends Handler {
  /**
   * @var string
   */
  private $file;
  
  /**
   * @var resource
   */
  private $stream = null;
  
  /**
   * @var bool
   */
  private $useLocking = false;
  
  /**
   * Construct file log handler.
   * @param string $filePath Log file path.
   * @param string $level Minimum log level, see {@see \Psr\Log\LogLevel}.
   * @param bool $useLocking Whether to lock the file before appending to ensure
   * atomicity of each write.
   */
  public function __construct($filePath, $level = LogLevel::DEBUG, $useLocking = false) {
    parent::__construct($level);
    if (!file_exists($filePath)) {
      $dir = dirname($filePath);
      if (!Utilities::dirExists($dir)) {
        trigger_error(tr('Could not create log directory: %1', $dir), E_USER_WARNING);
      }
      if (!touch($filePath)) {
        trigger_error(tr('Could not create log file: %1', $filePath), E_USER_WARNING);
      }
    }
    $this->file = $filePath;
    $this->useLocking = false;
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
    if ($this->useLocking)
      flock($this->stream, LOCK_EX);
    
    fwrite($this->stream, self::format($record));

    if ($this->useLocking)
      flock($this->stream, LOCK_UN);
  }
  
  /**
   * {@inheritdoc}
   */
  public function close() {
    if (is_resource($this->stream))
      fclose($this->stream);
  }
  
  /**
   * Format a log message for a log file.
   * @param array $record Log message array.
   * @return string Formatted log message followed by a line break.
   */
  public static function format(array $record) {
    $seconds = (int) $record['time'];
    $millis = floor(($record['time'] - $seconds) * 1000);
    $timestamp = date('Y-m-d H:i:s', $seconds);
    $timestamp .= sprintf('.%03d ', $millis);
    $timestamp .= date('P');
    $level = '[' . $record['level'] . ']';
    $message = '';
    $message .= Logger::interpolate($record['message'], $record['context']);
    if (isset($record['context']['file']))
      $message .= ' in ' . $record['context']['file'];
    if (isset($record['context']['line']))
      $message .= ' on line ' . $record['context']['line'];
    return $timestamp . ' ' . $level .  ' ' . $message . PHP_EOL;
  }
}