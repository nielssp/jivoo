<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Authorization;

use Jivoo\AccessControl\LoadableAuthorization;
use Jivoo\AccessControl\AuthorizationRequest;

/**
 * Authorize without the use of Access Control Lists. Calls the method
 * "authorize" in the current controller with the name of the action as a
 * parameter. The name of the method can be changed with the option "method".
 * @package Jivoo\AccessControl\Authorization
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
    return call_user_func(
      array($authRequest->controller, $this->options['method']),
      $authRequest->action
    );
  }
}