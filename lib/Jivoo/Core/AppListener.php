<?php

abstract class AppListener extends Module implements IEventListener {
  
  protected $handlers = array();
    
  public final function __construct(App $app) {
    parent::__construct($app);
  }
  public function getEventHandlers() {
    return $this->handlers;
  }
}