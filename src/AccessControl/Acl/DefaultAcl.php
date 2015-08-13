<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Acl;

use Jivoo\AccessControl\LoadableAcl;
use Jivoo\AccessControl\PermissionList;

/**
 * Default modifiable access control list. Permissions are independent of user.
 * Can be used to dynamically set permissions in the controller. Default is
 * to deny everything.
 */
class DefaultAcl extends LoadableAcl {
  /**
   * @var true|array Allowed permissions.
   */
  private $allow = array();
  
  /**
   * @var true|array Disallowed permissions.
   */
  private $deny = true;
  
  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission, $user = null) {
    if ($this->allow === true) {
      if (!isset($this->deny[$permission]))
        return true;
    }
    if (isset($this->allow[$permission]))
      return true;
    return false;
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