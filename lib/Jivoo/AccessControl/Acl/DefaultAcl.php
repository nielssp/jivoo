<?php
class DefaultAcl extends LoadableAcl {
  
  private $allow = array();
  private $deny = true;
  
  public function hasPermission(IRecord $user = null, $permission) {
    if ($this->allow === true) {
      return !isset($this->deny[$permission]);
    }
    return isset($this->allow[$permission]);
  }
  
  public function allow($permission = null) {
    if (!isset($permission)) {
      $this->allow = true;
      $this->deny = array();
    }
    else if (is_array($this->allow)) {
      $this->allow[$permission] = true;
    }
  }
  
  public function deny($permission = null) {
    if (!isset($permission)) {
      $this->allow = array();
      $this->deny = true;
    }
    else if (is_array($this->deny)) {
      $this->deny[$permission] = true;
    }
  }
}