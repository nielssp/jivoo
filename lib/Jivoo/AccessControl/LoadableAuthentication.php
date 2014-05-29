<?php
abstract class LoadableAuthentication extends Module implements IAuthentication {
  protected $options = array();

  public final function __construct(App $app, $options = array()) {
    parent::__construct($app);
    $this->options = array_merge($this->options, $options);
  }
  
  public function authenticate($data, IUserModel $userModel, IPasswordHasher $hasher) {
    return null;
  }
  
  public function deauthenticate(IRecord $user, IUserModel $userModel) { }
  
  public function cookie() {
    return false;
  }
  
  public function isStateLess() {
    return false;
  }
}