<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Controllers\Controller;

/**
 * Represents a request for authorization
 * @property-read Controller $controller Target controller of authorization.
 * @property-read string $action Target action.
 * @property-read mixed $user User data of requesting user.
 */
class AuthorizationRequest {
  /**
   * @var Controller Controller.
   */
  private $controller;
  
  /**
   * @var string Name of action.
   */
  private $action;
  
  /**
   * @var mixed User data.
   */
  private $user;
  
  /**
   * Construct authorization request.
   * @param Controller $controller Target controller of authorization.
   * @param string $action Target action.
   * @param mixed $user User data of requesting user.
   */
  public function __construct(Controller $controller, $action, $user = null) {
    $this->controller = $controller;
    $this->action = $action;
    $this->user = $user;
  }
  
  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    switch ($property) {
      case 'controller':
      case 'action':
      case 'user':
        return $this->$property;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }
}