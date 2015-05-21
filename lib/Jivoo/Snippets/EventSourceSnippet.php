<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Routing\EventSource;

/**
 * Used for pushing data to the client side using Server-Sent Events.
 * Not supported in all browsers, and works a bit strange sometimes, might be
 * better to use {@see CometSnippet}.
 */
abstract class EventSourceSnippet extends Snippet {
  /**
   * @var float Number of calls to {@see update()} per second.
   */
  protected $refreshRate = 1.0;

  /**
   * @var int Number of milliseconds the client should wait before reconnecting
   */
  protected $retry = 1000;
  
  /**
   * @var EventSource
   */
  private $source = null;
  
  /**
   * Update code, e.g. check for state changes. Use {@see trigger()} to send
   * data to the client.
   */
  protected abstract function update();

  /**
   * Create an event and send it to the client.
   * @param string $data Data.
   * @param int $id Optional event id.
   * @param string $event Optional event type.
   */
  public function trigger($data, $id = null, $event = null) {
    if (isset($event))
      $this->source->trigger($event, $data, $id);
    else
      $this->source->send($data, $id);
  }
  
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->source = new EventSource();
    $this->source->retry =  $this->retry;
    $this->source->start();
  }
  
  /**
   * {@inheritdoc}
   */
  public function get() {
    $max = floor(ini_get('max_execution_time') * 0.9);
    $start = $_SERVER['REQUEST_TIME'];
    $end = $start + $max;
    $delay = round(1 / $this->refreshRate * 1000000);
    $this->session->close();
    while (true) {
      usleep($delay);
      $this->update();
      if (time() >= $end)
        break;
    }
    $this->after(null);
    $this->source->stop();
  }
}
