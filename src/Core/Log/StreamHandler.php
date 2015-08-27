<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Jivoo\Core\Utilities;
use Psr\Log\LogLevel;

/**
 * Stream log handler.
 */
class StreamHandler extends HandlerBase {
  /**
   * @var resource Stream.
   */
  protected $stream = null;
  
  /**
   * @var bool Whether to use {@see flock} to lock the stream before appending.
   */
  protected $useLocking = false;
  
  /**
   * Construct stream log handler.
   * @param resource $stream Stream t o log to.
   * @param string $level Minimum log level, see {@see \Psr\Log\LogLevel}.
   * @param bool $useLocking Whether to lock the file before appending to ensure
   * atomicity of each write.
   */
  public function __construct($stream, $level = LogLevel::DEBUG, $useLocking = false) {
    parent::__construct($level);
    $this->stream = $stream;
    $this->useLocking = $useLocking;
  }
  
  /**
   * {@inheritdoc}
   */
  public function handle(array $record) {
    if (!is_resource($this->stream))
      return;
    if ($this->useLocking)
      flock($this->stream, LOCK_EX);
    
    fwrite($this->stream, self::format($record));
    fflush($this->stream);

    if ($this->useLocking)
      flock($this->stream, LOCK_UN);
  }
  
  /**
   * {@inheritdoc}
   */
  public function close() {
    if (is_resource($this->stream)) {
      fclose($this->stream);
      $this->stream = null;
    }
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