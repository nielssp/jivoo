<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Acl;

use Jivoo\Models\IRecord;

/**
 * An access control list implementation that calls the method 'hasPermission'
 * on the requesting user to check permissions. To use this ACL module,
 * implement a 'hasPermission' record method in the user model.
 */
class RecordAcl extends LoadableAcl {
  /**
   * {@inheritdoc}
   */
  public function hasPermission(IRecord $user = null, $permission) {
    if (!isset($user))
      return false;
    return $user->hasPermission($permission);
  }
  
}