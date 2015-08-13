<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Controllers\Controller;
use Jivoo\InvalidPropertyException;

/**
 * Represents a request for authorization
 * @property-read array|Linkable|string|null $route A route, see {@see Routing}.
 * @property-read mixed $user User data of requesting user.
 */
class AuthorizationRequest {
  /**
   * @var array|Linkable|string|null $route A route, see {@see Routing}.
   */
  private $route;
  
  /**
   * @var mixed User data.
   */
  private $user;
  
  /**
   * Construct authorization request.
   * @param array $route A route array, see {@see \Jivoo\Routing\Routing}.
   * @param mixed $user User data of requesting user.
   */
  public function __construct(array $route, $user = null) {
    $this->route = $route;
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
      case 'route':
      case 'user':
        return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
}