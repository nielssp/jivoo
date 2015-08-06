<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\App;
use Jivoo\InvalidPropertyException;

/**
 * Used for pushing data to the client side using Server-Sent Events.
 * @property int $lastId Last event id.
 * @property int $retry Number of milliseconds the client should wait before
 * reconnecting.
 * @property int $padding Number of bytes to pad the stream with. Required for
 * some older browsers (e.g. 2KB for IE).
 * @todo document
 */
class EventSource {
  /**
   * @var int
   */
  private $lastId = 0;

  /**
   * @var int
   */
  private $retry = 1000;

  /**
   * @var int
   */
  private $padding = 2000;

  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    switch ($property) {
      case 'lastId':
      case 'retry':
      case 'padding':
        return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Set value of a property.
   * @param string $property Property name.
   * @param mixed $value Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'lastId':
      case 'retry':
      case 'padding':
        $this->$property = $value;
        return;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Output a line.
   * @param string $line Line.
   */
  private function putLine($line = '') {
    echo $line . "\n";
  }
  
  /**
   * Flush output buffers.
   */
  private function flush() {
    ob_flush();
    flush();
  }
  
  /**
   * Start event source. Sends headers, padding and retry delay.
   */
  public function start() {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Access-Control-Allow-Origin: *');
    
    if (isset($_SERVER['HTTP_LAST_EVENT_ID']))
      $this->lastId = intval($_SERVER['HTTP_LAST_EVENT_ID']);
    else if (isset($_GET['lastEventId']))
      $this->lastId = intval($_GET['lastEventId']);
    
    if ($this->padding > 0)
      $this->putLine(':' . str_repeat(' ', $this->padding));
    $this->putLine('retry: ' . $this->retry);
    $this->flush();
  }
  
  /**
   * Stop event source and program execution.
   * @param App|null $app If set, the application's {@see App::stop} method will
   * be called instead of {@see exit}.
   */
  public function stop(App $app = null) {
    if (isset($app))
      $app->stop();
    exit;
  }

  /**
   * Send a message to the client (without event name).
   * @param string $data Data.
   * @param string $id Optional id.
   */
  public function send($data, $id = null) {
    if (!isset($id))
      $id = $this->lastId++;
    $this->putLine('id: ' . $id);
    $this->putLine('data: ' . $data);
    $this->putLine();
    $this->flush();
  }
  
  /**
   * Trigger an event.
   * @param string $event Event name.
   * @param string $data Event data.
   * @param string $id Optional id.
   */
  public function trigger($event, $data, $id = null) {
    if (!isset($id))
      $id = $this->lastId++;
    $this->putLine('event: ' . $event);
    $this->putLine('id: ' . $id);
    $this->putLine('data: ' . $data);
    $this->putLine();
    $this->flush();
  }
}