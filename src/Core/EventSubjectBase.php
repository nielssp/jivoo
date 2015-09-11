<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Event subject implementation.
 */
abstract class EventSubjectBase implements EventSubject {
  /**
   * @var string[] List of event names triggered by this module.
   */
  protected $events = array();

  /**
   * @var EventManager Event manager.
   */
  protected $e;

  /**
   * Construct event subject. Should always be called when extending this class.
   */
  public function __construct() {
    $this->e = new EventManager($this);
  }

  /**
   * {@inheritdoc}
   */
  public function attachEventHandler($name, $callback) {
    $this->e->attachHandler($name, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function on($name, $callback) {
    $this->e->attachHandler($name, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function one($name, $callback) {
    $this->e->attachHandler($name, $callback, true);
  }

  /**
   * {@inheritdoc}
   */
  public function attachEventListener(EventListener $listener) {
    $this->e->attachListener($listener);
  }

  /**
   * {@inheritdoc}
   */
  public function detachEventHandler($name, $callback) {
    $this->e->detachHandler($name, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function detachEventListener(EventListener $listener) {
    $this->e->detachListener($listener);
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents() {
    return $this->events;
  }

  /**
   * Trigger an event on this object.
   * @param string $name Name of event.
   * @param Event $event Event object.
   * @return bool False if event was stopped, true otherwise.
   */
  public function triggerEvent($name, Event $event = null) {
    return $this->e->trigger($name, $event);
  }
}