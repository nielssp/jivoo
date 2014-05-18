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
  
  private $subjectClass = null;
  
  private $parent = null;

  /**
   * Create a new event collection
   * @param IEventSubject $subject The object that triggers events in this collection
   */
  public function __construct(IEventSubject $subject, EventManager $parent = null) {
    $this->subject = $subject;
    $this->subjectClass = get_class($subject);
    $this->parent = $parent;
  }
  
  public function attachHandler($name, $callback) {
    if (!isset($this->events[$name]))
      $this->events[$name] = array();
    $this->events[$name][] = $callback;
  }
  
  public function attachListener(IEventListener $listener) {
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
  
  public function detachHandler($name, $callback) {
    if (!isset($this->events[$name]))
      return false;
    $index = array_search($callback, $this->events[$name], true);
    if ($index === false)
      return false;
    unset($this->events[$name][$index]);
    return true;
  }
  
  public function detachListener(IEventListener $listener) {
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
   * Execute all functions attached to an event
   * @param string $event Event name
   * @param Event $event Event object
   * @return bool False if event was stopped, true otherwise
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
    return true;
  }
}