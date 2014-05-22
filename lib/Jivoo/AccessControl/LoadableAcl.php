<?php
abstract class LoadableAcl extends Module implements IAcl {
  protected $options = array();

  public final function __construct(App $app, $options = array()) {
    parent::__construct($app);
    $this->options = array_merge($this->options, $options);
  }
}