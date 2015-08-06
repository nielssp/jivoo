<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * An access control list.
 */
interface IAcl {
  /**
   * Check if a user has a permission.
   * @param mixed $user User data.
   * @param string $permission Permission string.
   * @return bool True if user has permission, false if not.
   */
  public function hasPermission($permission, $user = null);
}