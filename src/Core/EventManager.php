<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Collection of events and handlers.
 */
class EventManager {
  /**
   * @var array Associative array where the key is an event name and the value
   * is an array of callbacks.
   */
  private $events = array();
  
  /**
   * @var EventSubject The object that triggers events in this collection.
  */
  private $subject = null;
  
  /**
   * @var string Subject class name.
   */
  private $subjectClass = null;
  
  /**
   * @var EventManager|null A parent EventManager.
   */
  private $parent = null;

  /**
   * Create a new event collection.
   * @param EventSubject $subject The object that triggers events in this collection.
   * @param EventManager|null $parent Optional parent event manager, will receive
   * the same events as child, but with class name of the subject prepended
   * to the event name (separated by a dot).
   */
  public function __construct(EventSubject $subject, EventManager $parent = null) {
    $this->subject = $subject;
    $this->subjectClass = get_class($subject);
    foreach ($this->subject->getEvents() as $name)
      $this->events[$name] = array();
    $this->parent = $parent;
  }
  
  /**
   * Attach an event handler to an event.
   * @param string $name Name of event to handle.
   * @param callback $callback Function to call. Function must accept an
   * {@see Event} as its first parameter.
   * @param bool $once Whether to invoke the handler only the first time the
   * event is triggered.
   */
  public function attachHandler($name, $callback, $once = false) {
    if (!isset($this->events[$name])) {
      if (strpos($name, '.') === false)
        throw new EventException(tr('Event subject "%1" does not have event "%2"', $this->subjectClass, $name));
      $this->events[$name] = array();
    }
    if ($once) {
      $self = $this; // 5.3 compatibility
      $this->events[$name][] = function($event) use($self, $name, $callback) {
        $ret = call_user_func($callback, $event);
        $self->detachHandler($name, $callback);
        return $ret;
      };
    }
    else {
      $this->events[$name][] = $callback;
    }
  }
  
  /**
   * Attach an event listener to object (i.e. multiple handlers to multiple
   * events).
   * @param EventListener $listener An event listener.
   */
  public function attachListener(EventListener $listener) {
    foreach ($listener->getEventHandlers() as $name => $method) {
      if (!is_string($name)) {
        $name = $method;
        if (strpos($method, '.') !== false) {
          $splits = explode('.', $method);
          $method = $splits[count($splits) - 1];
        }
      }
      $this->attachHandler($name, array($listener, $method));
    }
  }
  
  /**
   * Detach an already attached event handler.
   * @param string $name Name of event.
   * @param callback $callback Function to detach from event.
   * @return bool True if handler found and removed, false otherwise.
   */
  public function detachHandler($name, $callback) {
    if (!isset($this->events[$name]))
      return false;
    $index = array_search($callback, $this->events[$name], true);
    if ($index === false)
      return false;
    unset($this->events[$name][$index]);
    return true;
  }
  
  /**
   * Detach all handlers implemented by an event listener.
   * @param EventListener $listener An event listener.
   */
  public function detachListener(EventListener $listener) {
    foreach ($listener->getEventHandlers() as $name => $method) {
      if (!is_string($name)) {
        $name = $method;
        if (strpos($method, '.') !== false) {
          $splits = explode('.', $method);
          $method = $splits[count($splits) - 1];
        }
      }
      $this->detachHandler($name, array($listener, $method));
    }
  }

  /**
   * Execute all functions attached to an event.
   * @param string $event Event name.
   * @param Event $event Event object.
   * @return bool False if event was stopped, true otherwise.
   */
  public function trigger($name, Event $event = null) {
    if (!isset($event))
      $event = new Event($this->subject);
    if (isset($this->parent)) {
      if (!$this->parent->trigger($this->subjectClass . '.' . $name, $event))
        return false;
    }
    if (isset($this->events[$name])) {
      $event->name = $name;
      foreach ($this->events[$name] as $function) {
        $continue = call_user_func($function, $event);
        if ($event->stopped or $continue === false)
          return false;
      }
    }
    else if (strpos($name, '.') === false) {
      throw new EventException(tr('Event subject "%1" does not have event "%2"', $this->subjectClass, $name));
    }
    return true;
  }
}