<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Authorization;

use Jivoo\AccessControl\LoadableAuthorization;
use Jivoo\AccessControl\AuthorizationRequest;
use Jivoo\Controllers\ActionDispatcher;

/**
 * Authorize based on name of controller and action, e.g. checks permission
 * "PostsAdmin.view" for the action "view" in "PostsAdminController".
 * Uses access control lists.
 */
class ActionAuthorization extends LoadableAuthorization {
  /**
   * {@inheritdoc}
   */
  public function authorize(AuthorizationRequest $authRequest) {
    $route = $authRequest->route; 
    if ($route['dispatcher'] instanceof ActionDispatcher) {
      $controller = $this->m->Controllers->getController($route['controller']);
      $permission = str_replace('\\', '.', $route['controller']) . '.' . $route['action'];
      return $this->Auth->hasPermission($permission);
    }
  }
}