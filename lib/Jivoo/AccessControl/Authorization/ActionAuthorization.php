<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Authorization;

use Jivoo\AccessControl\LoadableAuthorization;
use Jivoo\AccessControl\AuthorizationRequest;

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
    \Jivoo\Core\Logger::debug('authorize ' . $authRequest->controller->getName() . '.' . $authRequest->action);
    return $this->Auth->hasPermission(
      $authRequest->controller->getName() . '.' . $authRequest->action
    );
  }
}