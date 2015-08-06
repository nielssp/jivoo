<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Authorization;

use Jivoo\AccessControl\LoadableAuthorization;
use Jivoo\AccessControl\AuthorizationRequest;
use Jivoo\Snippets\SnippetDispatcher;

/**
 * Authorize based on name of snippet and action, e.g. checks permission
 * "Comment.Write" for the snippet "Comment\Write".
 * Uses access control lists.
 */
class SnippetAuthorization extends LoadableAuthorization {
  /**
   * {@inheritdoc}
   */
  public function authorize(AuthorizationRequest $authRequest) {
    $route = $authRequest->route; 
    if ($route['dispatcher'] instanceof SnippetDispatcher) {
      $permission = str_replace('\\', '.', $route['snippet']);
      return $this->Auth->hasPermission($permission);
    }
  }
}