<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

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
  
  private $lastId = 0;
  
  private $retry = 1000;
  
  private $padding = 2000;
  
  public function __get($property) {
    switch ($property) {
      case 'lastId':
      case 'retry':
      case 'padding':
        return $this->$property;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  public function __set($property, $value) {
    switch ($property) {
      case 'lastId':
      case 'retry':
      case 'padding':
        $this->$property = $value;
        return;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  private function putLine($line = '') {
    echo $line . "\n";
  }
  
  private function flush() {
    ob_flush();
    flush();
  }
  
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
  
  public function stop() {
    exit;
  }

  public function send($data, $id = null) {
    if (!isset($id))
      $id = $this->lastId++;
    $this->putLine('id: ' . $id);
    $this->putLine('data: ' . $data);
    $this->putLine();
    $this->flush();
  }
  
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