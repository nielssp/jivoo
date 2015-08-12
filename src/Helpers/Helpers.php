<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\LoadEvent;
use Jivoo\Core\Utilities;

/**
 * Helpers module. All helpers added to the module, can be accessed as
 * read-only properties.
 */
class Helpers extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $events = array('beforeLoadHelper', 'afterLoadHelper');
  
  /**
   * @var Helper[] Associative array of loaded helpers
   */
  private $helpers = array();
  
  /**
   * @var string[] Mapping of helper names and classes.
   */
  private $helperClasses = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->lazy('View')->addFunction('helper', array($this, 'getHelper'));
  }
  
  /**
   * Add helper class.
   * @param string $class Class name.
   * @param string $name Helper name. Is derived from class name if null.
   */
  public function addHelper($class, $name = null) {
    if (!isset($name)) {
      $name = $class;
      if (preg_match('/([^\\\\]+?)(?:Helper)?$/', $class, $matches) === 1) {
        $name = $matches[1];
      }
    }
    $this->helperClasses[$name] = $class;
  }
  
  /**
   * Get a helper instance
   * @param string $name Helper name
   * @return Helper A helper object
   */
  public function getHelper($name) {
    if (!isset($this->helpers[$name])) {
      $class = $this->app->n('Helpers\\' . $name . 'Helper');
      if (!class_exists($class)) {
        if (isset($this->helperClasses[$name])) {
          $class = $this->helperClasses[$name];
        }
        else if (strpos($name, '\\') !== false) {
          $class = $name . 'Helper';
          $name = Utilities::getClassName($name);
        }
        else {
          $class = 'Jivoo\Helpers\\' . $name . 'Helper';
        }
      }
      $this->triggerEvent('beforeLoadHelper', new LoadHelperEvent($this, $name));
      Utilities::assumeSubclassOf($class, 'Jivoo\Helpers\Helper');
      $this->helpers[$name] = new $class($this->app);
      $this->triggerEvent('afterLoadHelper', new LoadHelperEvent($this, $name, $this->helpers[$name]));
    }
    return $this->helpers[$name];
  }

  /**
   * Get multiple helpers.
   * @param string[] $names Names of helpers.
   * @return Helper[] Helper objects.
   */
  public function getHelpers($names) {
    $helpers = array();
    foreach ($names as $name) {
      $helper = $this->getHelper($name);
      if (strpos($name, '\\') !== false)
        $name = Utilities::getClassName($name);
      $helpers[$name] = $helper;
    }
    return $helpers;
  }
  
  /**
   * Whether the helper exists.
   * @param string $name Helper name
   * @return bool True if it exists, false otherwise.
   */
  public function hasHelper($name) {
    return isset($this->helpers[$name]);
  }
  
  /**
   * Get a helper instance
   * @param string $name Helper name
   * @return Helper|null A helper object or null on failure
   */
  public function __get($name) {
    return $this->getHelper($name);
  }

  /**
   * Whether the helper exists.
   * @param string $name Helper name
   * @return bool True if it exists, false otherwise.
   */
  public function __isset($name) {
    return $this->hasHelper($name);
  }
}

/**
 * Event sent before and after a helper has been loaded
 */
class LoadHelperEvent extends LoadEvent { }
