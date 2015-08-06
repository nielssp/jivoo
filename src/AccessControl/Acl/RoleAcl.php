<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Acl;

use Jivoo\AccessControl\LoadableAcl;
use Jivoo\Models\IBasicRecord;
use Jivoo\AccessControl\IPermissionList;
use Jivoo\Core\Logger;
use Jivoo\AccessControl\PermissionList;
use Jivoo\AccessControl\Jivoo\AccessControl;
use Jivoo\AccessControl\InvalidRoleException;

/**
 * An access control list implementation that assumes the user data has a
 * 'role' field (can be changed with the 'field' option) that can be accessed
 * using array access (e.g. as implemented by {@see IBasicRecord})
 */
class RoleAcl extends LoadableAcl {
  /**
   * {@inheritdoc}
   */
  protected $options = array(
    'field' => 'role',
    'default' => 'guest'
  );
  
  /**
   * @var IPermissionList[]
   */
  private $roles = array();

  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission, $user = null) {
    $role = $this->options['default'];
    $field = $this->options['field'];
    if (isset($user) and isset($user[$field]))
      $role = $user[$field];
    if (!isset($this->roles[$role])) {
      Logger::log(tr('Undefined role: %1', $role));
      return false;
    }
    return $this->roles[$role]->hasPermission($permission);
  }

  /**
   * Get permissions of a role, or create the role if it doesn't exist.
   * @param string $role Role name or id.
   * @return IPermissionList $permissions Permission list.
   */
  public function __get($role) {
    if (!isset($this->roles[$role]))
      $this->createRole($role);
    return $this->roles[$role];
  }

  /**
   * Get permissions of a role.
   * @param string $role Role name or id.
   * @param IPermissionList $permissions Permission list.
   */
  public function __set($role, $permissions) {
    $this->roles[$role] = $permissions;
  }
  
  /**
   * Add a role.
   * @param string $role Role name or id.
   * @param IPermissionList $permissions Permission list.
   */
  public function addRole($role, IPermissionList $permissions) {
    $this->roles[$role] = $permissions;
  }
  
  /**
   * Create a role (an instance of {@see PermissionList}.
   * @param string $role Role name or id.
   * @param string|null $parent Optional parent role.
   * @return DefaultAcl Permission list for role.
   * @throws InvalidRoleException If the parent role is undefined.
   */
  public function createRole($role, $parent = null) {
    $permissions = new PermissionList($this->app);
    if (isset($parent)) {
      if (!isset($this->roles[$parent]))
        throw new InvalidRoleException(tr('Undefined role: %1', $parent));
      $permissions->inheritFrom($this->roles[$parent]);
    }
    $this->roles[$role] = $permissions;
    return $permissions;
  }
}