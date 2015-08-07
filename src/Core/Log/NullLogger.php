<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Psr\Log\LoggerInterface;

/**
 * A logger that doesn't log anything. 
 */
class NullLogger implements LoggerInterface {
  /**
   * {@inheritdoc}
   */
  public function emergency($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function alert($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function critical($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function error($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function warning($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function notice($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function info($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function debug($message, array $context = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
  }
}