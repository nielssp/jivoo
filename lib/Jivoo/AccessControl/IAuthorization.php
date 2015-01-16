<?php
/**
 * A method of authorization.
 * @package Jivoo\AccessControl
 */
interface IAuthorization {
  /**
   * Process a request for authorization.
   * @param AuthorizationRequest $authRequest Request of authorization.
   * @return bool True if authorized, false otherwise.
   */
  public function authorize(AuthorizationRequest $authRequest);
}
