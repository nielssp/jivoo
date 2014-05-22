<?php
abstract class LoadableAuthorization extends Module implements IAuthorization {
  protected $options = array();
  
  protected $Auth;

  public final function __construct(App $app, $options = array(), AuthHelper $Auth) {
    parent::__construct($app);
    $this->options = array_merge($this->options, $options);
    $this->Auth = $Auth;
  }
}