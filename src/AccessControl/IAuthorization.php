<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * A method of authorization.
 */
interface Authorization {
  /**
   * Process a request for authorization.
   * @param AuthorizationRequest $authRequest Request of authorization.
   * @return bool True if authorized, false otherwise.
   */
  public function authorize(AuthorizationRequest $authRequest);
}
