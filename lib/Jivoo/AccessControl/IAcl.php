<?php
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