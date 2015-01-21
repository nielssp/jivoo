<?php
/**
 * Object that handles multiple events. The implemented events are defined by
 * an associative array returned from the method getEventHandlers.
 * @package Jivoo\Core
 */
interface IEventListener {
  /**
   * Get list of event handlers.
   * @return string[] An associative array from event names to method names.
   * @todo Event names can be omitted and some dot-notation used, explain.
   */
  public function getEventHandlers();
}