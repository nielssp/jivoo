<?php
/**
 * A global event listener handling event at application and module level.
 * Subclasses can be attached to the application by adding their names to
 * the "listeners"-array in 'app.json'.
 * @package Jivoo\Core
 */
abstract class AppListener extends Module implements IEventListener {
  /**
   * @var string[] Associative array of event names and handler methods.
   */
  protected $handlers = array();

  /**
   * Construct.
   * @param App $app Associated application.
   */
  public final function __construct(App $app) {
    parent::__construct($app);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getEventHandlers() {
    return $this->handlers;
  }
}