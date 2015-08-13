<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * A global event listener handling event at application and module level.
 * Subclasses can be attached to the application by adding their names to
 * the "listeners"-array in 'app.json'.
 */
abstract class AppListener extends Module implements EventListener {
  /**
   * @var string[] Associative array of event names and handler methods.
   */
  protected $handlers = array();

  /**
   * Construct.
   * @param App $app Associated application.
   */
  public final function __construct(App $app) {
    $this->inheritElements('modules');
    $this->inheritElements('handlers');
    parent::__construct($app);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getEventHandlers() {
    return $this->handlers;
  }
}