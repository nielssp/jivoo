<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Log;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * A handler that proxies log messages to another PSR-3 logger.
 */
class LoggerHandler extends Handler implements LoggerAwareInterface {
  /**
   * @var LoggerInterface
   */
  private $logger;
  
  /**
   * Construct PSR-3 logger handler.
   * @param LoggerInterface $logger PSR-3 logger.
   * @param int $level Minimum log level, see {@see LogLevel}.
   */
  public function __construct(LoggerInterface $logger, $level = LogLevel::DEBUG) {
    parent::__construct($level);
    $this->logger = $logger;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(array $record) {
    $this->logger->log($record['level'], $record['message'], $record['context']);
  }
}
