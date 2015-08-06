<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo {
  /**
   * Jivoo exception, all exceptions thrown by Jivoo must implement this
   * interface. 
   */
  interface Exception {}
  
  /**
   * Thrown when method or function argument is not of the expected type.
   */
  class InvalidArgumentException extends \InvalidArgumentException
    implements Exception {}

  /**
   * Thrown when method is undefined.
   */
  class InvalidMethodException extends \BadMethodCallException
    implements Exception {}

  /**
   * Thrown when property is undefined.
   */
  class InvalidPropertyException extends \OutOfRangeException
    implements Exception {}

  /**
   * Thrown when a class is undefined or invalid.
   */
  class InvalidClassException extends InvalidArgumentException
    implements Exception {}
}

namespace Jivoo\AccessControl {
  /**
   * AccessControl exception.
   */
  interface AccessControlException extends \Jivoo\Exception {}
  
  /**
   * Thrown when a hashing algorithm is unsupported.
   */
  class UnsupportedHashTypeException extends \RangeException
    implements AccessControlException {}
  
  /**
   * Thrown when a role is undefined.
   */
  class InvalidRoleException extends \DomainException
    implements AccessControlException {}
}

namespace Jivoo\ActiveModels {
  use Jivoo\InvalidClassException;

    /**
   * ActiveModels exception.
   */
  interface ActiveModelsException extends \Jivoo\Exception {}
  
  /**
   * Thrown when an ActiveModel is incorrectly defined.
   */
  class InvalidActiveModelException extends InvalidClassException
    implements ActiveModelsException {}
  
  /**
   * Thrown when an ActiveModel association is incorrectly defined.
   */
  class InvalidAssociationException extends InvalidActiveModelException {}
  
  /**
   * Thrown when an ActiveModel mixin is incorrectly defined.
   */
  class InvalidMixinException extends InvalidActiveModelException {}
}

namespace Jivoo\Core {
  /**
   * Core exception.
   */
  interface CoreException extends \Jivoo\Exception {}
  
  /**
   * Thrown when a boot environment is undefined.
   */
  class InvalidEnvironmentException extends \DomainException
    implements CoreException {}
  
  /**
   * Thrown when modules are loaded in the wrong order.
   */
  class LoadOrderException extends \LogicException
    implements CoreException {}
  
  /**
   * Thrown when a JSON string is invalid.
   */
  class JsonException extends \RuntimeException
    implements CoreException {}
  
  /**
   * Thrown when a PHP error is caught.
   */
  class ErrorException extends \ErrorException
    implements CoreException {}
  
  /**
   * Thrown when a third-party library could not be imported.
   */
  class VendorException extends \RuntimeException
    implements CoreException {}
  
  /**
   * Thrown when configuration is invalid or missing.
   */
  class ConfigurationException extends \RuntimeException 
    implements CoreException {}
}

namespace Jivoo\Core\Store  {
  /**
   * Store exception. 
   */
  interface StoreException extends \Jivoo\Core\CoreException {}
  
  /**
   * Thrown when a store is unreadable or unwritable.
   */
  class AccessException extends \RuntimeException
    implements StoreException {}

  /**
   * Thrown when a lock could not be acquired.
   */
  class LockException extends AccessException
    implements StoreException {}
  
  /**
   * Thrown when a state has already been closed (or hasn't been opened). 
   */
  class NotOpenException extends \LogicException
    implements StoreException {}
}

namespace Jivoo\Databases  {
  use Jivoo\InvalidClassException;
    
    /**
   * Databases exception. 
   */
  interface DatabasesException extends \Jivoo\Exception {}
  
  /**
   * Thrown when a store is unable is unreadable or unwritable.
   */
  class InvalidTableException extends \DomainException
    implements DatabasesException {}
  
  /**
   * Thrown when a database query fails.
   */
  class QueryException extends \RuntimeException
    implements DatabasesException {}
  
  /**
   * Thrown when unsupported types are encountered in a database.
   */
  class TypeException extends QueryException {}
  
  /**
   * Thrown when a database connection fails.
   */
  class ConnectionException extends \RuntimeException
    implements DatabasesException {}
  
  /**
   * Thrown when a database connection fails because the configuration is 
   * missing or invalid.
   */
  class ConfigurationException extends \Jivoo\Core\ConfigurationException
    implements DatabasesException {}
  
  /**
   * Thrown when a table schema is undefined.
   */
  class InvalidSchemaException extends InvalidClassException
    implements DatabasesException {}
}

namespace Jivoo\Extensions {
  /**
   * Thrown when an extension is invalid or could not be found.
   */
  class InvalidExtensionException extends \RuntimeException
    implements \Jivoo\Exception {}
}

namespace Jivoo\Helpers  {
  use Jivoo\InvalidArgumentException;

  /**
   * Thrown from the form helper.
   */
  class FormHelperException extends InvalidArgumentException {}
}

namespace Jivoo\Migrations  {
  /**
   * Thrown when a migration fails.
   */
  class MigrationException extends \RuntimeException
    implements \Jivoo\Exception {}
}

namespace Jivoo\Models  {
  use Jivoo\InvalidClassException;
  use Jivoo\InvalidArgumentException;
    
  /**
   * Models exception. 
   */
  interface ModelsException extends \Jivoo\Exception {}
  
  /**
   * Thrown when a model is invalid or could not be found.
   */
  class InvalidModelException extends InvalidClassException
    implements ModelsException {}
  
  /**
   * Thrown when an enum is invalid or could not be found.
   */
  class InvalidEnumException extends InvalidClassException
    implements ModelsException {}
  
  /**
   * Thrown when a selection is invalid or incompatible with the model.
   */
  class InvalidSelectionException extends InvalidArgumentException
    implements ModelsException {}
  
  /**
   * Thrown when a data type is undefined.
   */
  class InvalidDataTypeException extends \DomainException
    implements ModelsException {}
}

namespace Jivoo\Routing  {
  use Jivoo\InvalidArgumentException;

  /**
   * Routing exception.
   */
  interface RoutingException extends \Jivoo\Exception {}
  
  /**
   * Thrown when HTTP response headers have already been sent.
   */
  class HeadersSentException extends \RuntimeException
    implements RoutingException {}
  
  /**
   * Thrown when a HTTP status is unknown.
   */
  class InvalidStatusException extends \DomainException
    implements RoutingException {}
  
  /**
   * Thrown when a route is invalid.
   */
  class InvalidRouteException extends InvalidArgumentException
    implements RoutingException {}
  
  /**
   * Thrown when a response is invalid.
   */
  class InvalidResponseException extends InvalidArgumentException
    implements RoutingException {}
  
  /**
   * When thrown, the current response is replaced.
   */
  class ResponseOverrideException extends \RuntimeException
    implements RoutingException {
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
  
  /**
   * Thrown to indicate a client error.
   */
  class ClientException extends \RuntimeException implements RoutingException {
    /**
     * @var int Optional HTTP status code overrride.
     */
    public $status = null;
  }
  
  /**
   * Can be used in an action to send the client to the error page.
   */
  class NotFoundException extends ClientException {
    /**
     * {@inheritdoc}
     */
    public $status = Http::NOT_FOUND;
  }
  
  /**
   * Can be used in an action to send the client to the error page.
   */
  class NotAcceptableException extends ClientException {
    /**
     * {@inheritdoc}
     */
    public $status = Http::NOT_ACCEPTABLE;
  }
}

namespace Jivoo\Setup {
  use Jivoo\Exception;

  /**
   * Thrown when an installer or updaer fails.
   */
  class SetupException extends \RuntimeException implements Exception {}
}

namespace Jivoo\Themes {
  use Jivoo\Extensions\InvalidExtensionException;

    /**
   * Thrown when a theme is invalid or could not be found.
   */
  class InvalidThemeException extends InvalidExtensionException {}
}

namespace Jivoo\View {
  use Jivoo\InvalidArgumentException;
  use Jivoo\Exception;
      
  /**
   * Thrown when a template is invalid or could not be found.
   */
  class InvalidTemplateException extends InvalidArgumentException {}
  
  /**
   * Thrown when a template macro is undefined.
   */
  class InvalidMacroException extends InvalidTemplateException {}
  
  /**
   * Thrown when tesource type is unknown.
   */
  class ResourceTypeException extends \DomainException
    implements Exception {}
}