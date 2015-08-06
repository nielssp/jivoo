<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * A permission list.
 */
interface IPermissionList {
  /**
   * Check if a permission exists in the list.
   * @param string $permission Permission string.
   * @return bool True if permission exists, false if not.
   */
  public function hasPermission($permission);
  
  /**
   * Check if a permission exists in the list.
   * @param string $permission Permission string.
   * @return bool True if permission exists, false if not.
   */
  public function __isset($permission);
}