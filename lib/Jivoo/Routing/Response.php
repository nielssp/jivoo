<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\Utilities;
use Jivoo\InvalidPropertyException;

/**
 * Base class for HTTP responses.
 * @property int $status HTTP status code.
 * @property string $type Response type.
 * @property-read string|null $cache Either 'public', 'private' or null.
 * @property-read string $body Response body.
 * @property int|null $modified Time of last modification, for caching purposes.
 * @property int|null $maxAge Maximum life of cache.
 */
abstract class Response {
  /**
   * @var int HTTP status code
   */
  private $status;
  
  /**
   * @var string Response type.
   */
  private $type;
  
  /**
   * @var string Caching.
   */
  private $cache = null;
  
  /**
   * @var int Modified time.
   */
  private $modified = null;
  
  /**
   * @var int Max life time.
   */
  private $maxAge = null;

  /**
   * Construct response.
   * @param int $status HTTP status code, e.g. 200 for OK.
   * @param string $type Response type, either a MIME type or a file extension
   * known by {@see Utilities::convertType()}.
   */
  public function __construct($status, $type) {
    $this->status = $status;
    $this->type = Utilities::convertType($type);
  }

  /**
   * Get value of property.
   * @param string $name Property name.
   * @return mixed Value of property.
   * @throws InvalidPropertyException If unknown property.
   */
  public function __get($property) {
    switch ($property) {
      case 'status':
      case 'type':
      case 'cache':
      case 'modified':
      case 'maxAge':
        return $this->$property;
      case 'body':
        return $this->getBody();
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Set value of property.
   * @param string $name Property name.
   * @param string $value Value of property.
   * @throws InvalidPropertyException If unknown property.
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'status':
      case 'modified':
      case 'maxAge':
        $this->$property = $value;
        return;
      case 'type':
        $this->type = Utilities::convertType($value);
        return;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }


  /**
   * Whether or not a property is set.
   * @param string $name Property name.
   * @return bool True if property set.
   * @throws InvalidPropertyException If unknown property.
   */
  public function __isset($property) {
    return isset($this->$property);
  }

  /**
   * Get response body.
   * @return string Response body.
   */
  public abstract function getBody();

  /**
   * Set cache settings.
   * @param string $public Public or private.
   * @param int|string $expires Time on which cache expires. Can be a UNIX
   * timestamp or a string used with {@see strtotime()}.
   */
  public function cache($public = true, $expires = '+1 year') {
    if (!is_int($expires))
      $expires = strtotime($expires);
    $this->maxAge = $expires - time();
    $this->cache = $public ? 'public' : 'private';
  }
}

