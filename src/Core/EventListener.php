<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Object that handles multiple events. The implemented events are defined by
 * an associative array returned from the method getEventHandlers.
 */
interface EventListener {
  /**
   * Get list of event handlers.
   * @return string[] An associative array from event names to method names.
   * @todo Event names can be omitted and some dot-notation used, explain.
   */
  public function getEventHandlers();
}