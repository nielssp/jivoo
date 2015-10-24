<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\InvalidPropertyException;
use Jivoo\InvalidMethodException;
use Psr\Log\LoggerAwareInterface as LoggerAware;
use Psr\Log\LoggerInterface;
use Jivoo\Core\Log\NullLogger;
use Jivoo\Core\Store\Document;

/**
 * A module is part of an application.
 */
abstract class Module extends EventSubjectBase implements LoggerAware {
  /**
   * @var string[] Names of modules required by this module.
   */
  protected $modules = array();

  /**
   * @var App Application associated with module.
   */
  protected $app = null;
  
  /**
   * @var ModuleLoader Collection of loaded modules.
   */
  protected $m = null;
  
  /**
   * @var LoggerInterface Application logger, defaults to {@see NullLogger} if
   * the application hasn't been set.
   */
  protected $logger;

  /**
   * @var Config Module configuration.
   */
  protected $config = null;

  /**
   * Construct module. Should always be called when extending this class.
   * @param App|null $app Associated application if any.
   */
  public function __construct(App $app = null) {
    if (isset($app)) {
      $this->setApp($app);
    }
    else {
      parent::__construct();
      $this->logger = new NullLogger();
      $this->config = new Document();
    }
  }
  
  /**
   * Associate module with application and find dependencies.
   * @param App $app Application.
   * @throws InvalidModuleException If a module in the {@see $modules} array
   * is not available.
   */
  public function setApp(App $app) {
    $this->inheritElements('modules');
    $this->app = $app;
    $this->config = $app->config['user'];
    $this->logger = $app->logger;
    if (isset($app->m)) {
      $this->m = $app->m;
      foreach ($this->modules as $module) {
        if (!isset($this->m->$module))
          throw new InvalidModuleException('Module "' . $module . '" not loaded. Required by ' . get_class($this));
        $this->m->__get($module);
      }
    }

    $this->e = new EventManager($this, $this->app->eventManager);
  }
  
  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    if ($property == 'config')
      return $this->config;
    if (isset($this->m))
      return $this->m->getProperty($property);
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Set value of a property.
   * @param string $property Property name.
   * @param mixed $value Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __set($property, $value) {
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Whether or not a property is set, i.e. not null.
   * @param string $property Property name.
   * @return bool True if not null, false otherwise.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __isset($property) {
    return $this->m->hasProperty($property);
  }

  /**
   * Unset value of a property.
   * @param string $property Property name.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __unset($property) {
    $this->__set($property, null);
  }

  /**
   * Call a method.
   * @param string $method Method name.
   * @param mixed[] $paramters List of parameters.
   * @return mixed Return value.
   * @throws InvalidMethodException If method is not defined.
   */
  public function __call($method, $parameters) {
    if (isset($this->m))
      return $this->m->callMethod($method, $parameters);
    throw new InvalidMethodException(tr('Invalid method: %1', $method));
  }
  
  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }
  
  /**
   * Get logger.
   * @return LoggerInterface Logger.
   */
  public function getLogger() {
    return $this->logger;
  }
  
  /**
   * Combine array property default values from parent classes.
   * @param string Name of property.
   */
  protected function inheritElements($property) {
    $value = $this->$property;
    $parent = new \ReflectionClass(get_parent_class($this));
    while ($parent->name != 'Jivoo\Core\Module') {
      $defaults = $parent->getDefaultProperties();
      if (isset($defaults[$property]) and is_array($defaults[$property]))
        $value = array_merge($value, $defaults[$property]);
      $parent = $parent->getParentClass();
    }
    $this->$property = array_unique($value);
  }

  /**
   * Get the absolute path of a file or directory.
   * @param string $key Location-identifier, e.g. 'app'.
   * @param string $path File or directory name.
   * @return string Absolute path.
   */
  public function p($key, $path = null) {
    assume(isset($this->app));
    return $this->app->p($key, $path);
  }
}