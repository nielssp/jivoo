<?php
/**
 * Collection of events and handlers
 * @package Core
 */
class EventManager {
  /**
   * @var array Associative array where the key is an event name and the value
   * is an array of callbacks
   */
  private $events = array();

  /**
   * @var IEventSubject The object that triggers events in this collection
  */
  private $subject = null;

  /**
   * Create a new event collection
   * @param IEventSubject $subject The object that triggers events in this collection
   */
  public function __construct(IEventSubject $subject) {
    $this->subject = $subject;
    foreach ($this->subject->getEvents() as $name) {
      $this->events[$name] = array();
    }
  }
  
  public function attachHandler($name, $callback) {
    if (!isset($this->events[$name]))
      throw new InvalidEventException(tr(
        'Subject of class %1 does not have an event called "%2".',
        get_class($this->subject), $name
      ));
    $this->events[$name][] = $callback;
  }
  
  public function attachListener(IEventListener $listener) {
    foreach ($listener->getEventHandlers() as $name => $method) {
      if (!is_string($name))
        $name = $method;
      $this->attachHandler($name, array($listener, $method));
    }
  }
  
  public function detachHandler($name, $callback) {
    if (!isset($this->events[$name]))
      throw new InvalidEventException(tr(
        'Subject of class %1 does not have an event called "%2".',
        get_class($this->subject), $name
      ));
    $index = array_search($callback, $this->events[$name], true);
    if ($index === false)
      return true;
    unset($this->events[$name][$index]);
    return true;
  }
  
  public function detachListener(IEventListener $listener) {
    foreach ($listener->getEventHandlers() as $name => $method) {
      if (!is_string($name))
        $name = $method;
      $this->detachHandler($name, array($listener, $method));
    }
  }

  /**
   * Execute all functions attached to an event
   * @param string $event Event name
   * @param mixed $eventArgs Event arguments
   */
  public function trigger($name, Event $event = null) {
    if (isset($this->events[$name])) {
      if (!isset($event))
        $event = new Event($this->sender);
      foreach ($this->events[$name] as $function) {
        call_user_func($function, $event);
        if ($event->stopped)
          return;
      }
    }
  }
}

class InvalidEventException extends Exception { }