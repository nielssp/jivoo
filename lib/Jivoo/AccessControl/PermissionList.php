<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * Modificable permission list. Default is to deny everything.
 */
class PermissionList implements IPermissionList {
  /**
   * @var true|array Allowed permissions.
   */
  private $allow = array();
  
  /**
   * @var true|array Disallowed permissions.
   */
  private $deny = true;
  
  /**
   * @var IPermissionList
   */
  private $parent = null;
  
  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission) {
    if ($this->allow === true) {
      if (!isset($this->deny[$permission]))
        return true;
    }
    if (isset($this->allow[$permission]))
      return true;
    if (isset($this->parent))
      return $this->parent->hasPermission($permission);
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($permission) {
    return $this->hasPermission($permission);
  }
  
  /**
   * Set inheritted permissions.
   * @param IPermissionList $parent Parent.
   */
  public function inheritFrom(IPermissionList $parent) {
    $this->parent = $parent;
  }
  
  /**
   * Allow a permission.
   * @param string|null $permission Permission string. If null all permissions
   * are allowed (unless denied using {@see deny}.
   */
  public function allow($permission = null) {
    if (!isset($permission)) {
      $this->allow = true;
      $this->deny = array();
    }
    else if (is_array($this->allow)) {
      $this->allow[$permission] = true;
    }
    else if (isset($this->deny[$permission])) {
      unset($this->deny[$permission]);
    }
  }

  /**
   * Disallow a permission.
   * @param string|null $permission Permission string. If null all permissions
   * are disallowed (unless allowed using {@see allow}.
   */
  public function deny($permission = null) {
    if (!isset($permission)) {
      $this->allow = array();
      $this->deny = true;
    }
    else if (is_array($this->deny)) {
      $this->deny[$permission] = true;
    }
    else if (isset($this->allow[$permission])) {
      unset($this->allow[$permission]);
    }
  }
}