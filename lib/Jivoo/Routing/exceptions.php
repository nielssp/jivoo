<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\Exception;

/**
 * Headers have already been sent and cannot be changed.
 */
class HeadersAlreadySentException extends Exception { }

/**
 * A routing exception.
 */
class RoutingException extends Exception { }

/**
 * Invalid route.
 */
class InvalidRouteException extends RoutingException { }

/**
 * Invalid response.
 */
class InvalidResponseException extends RoutingException { }

/**
 * Can be used in an action to send the client to the error page.
 */
class NotFoundException extends RoutingException { }

/**
 * When thrown, the current response is replaced.
 */
class ResponseOverrideException extends RoutingException {
  /**
   * @var Response New response object.
   */
  private $response;

  /**
   * Construct response override.
   * @param Response $response New response object.
   */
  function __construct(Response $response) {
    $this->response = $response;
  }

  /**
   * Get the response object.
   * @return Response Response object.
   */
  function getResponse() {
    return $this->response;
  }
}