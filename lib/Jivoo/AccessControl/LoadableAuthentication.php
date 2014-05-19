<?php
abstract class LoadableAuthentication extends Module implements IAuthentication {
  protected $options = array();

  public final function __construct(App $app, $options = array()) {
    parent::__construct($app);
    $this->options = array_merge($this->options, $options);
  }
  
  public function authenticate($data, IUserModel $userModel) {
    return null;
  }
  
  public function cookie() {
    return false;
  }
}