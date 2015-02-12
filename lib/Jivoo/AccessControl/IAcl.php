<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Models\IRecord;

/**
 * An access control list.
 * @package Jivoo\AccessControl
 */
interface IAcl {
  /**
   * Check if a user has a permission.
   * @param IRecord $user Record of yser
   * @param string $permission Permission string.
   * @return bool True if user has permission, false if not.
   */
  public function hasPermission(IRecord $user = null, $permission);
}