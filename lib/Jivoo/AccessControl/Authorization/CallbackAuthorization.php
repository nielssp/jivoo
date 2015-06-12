<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Authorization;

use Jivoo\AccessControl\LoadableAuthorization;
use Jivoo\AccessControl\AuthorizationRequest;
use Jivoo\Controllers\ActionDispatcher;
use Jivoo\Snippets\SnippetDispatcher;

/**
 * Authorize without the use of Access Control Lists. Calls the method
 * "authorize" in the current controller or snippet with the requesting user as 
 * a parameter along with the name of the action (if controller). Return true
 * to grant authorization or false to deny. The name of the method can be
 * changed with the option "method".
 */
class CallbackAuthorization extends LoadableAuthorization {
  /**
   * {@inheritdoc}
   */
  protected $options = array(
    'method' => 'authorize',
  );

  /**
   * {@inheritdoc}
   */
  public function authorize(AuthorizationRequest $authRequest) {
    $route = $authRequest->route;
    if ($route['dispatcher'] instanceof ActionDispatcher) {
      $controller = $this->m->Controllers->getController($route['controller']);
      return call_user_func(
        array($controller, $this->options['method']),
        $route['action']
      );
    }
    else if ($route['dispatcher'] instanceof SnippetDispatcher) {
      $snippet = $this->m->Snippets->getSnippet($route['snippet']);
      return call_user_func(
        array($snippet, $this->options['method'])
      );
    }
  }
}