<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Models\BasicRecord;
use Jivoo\Assets\Assets;
use Jivoo\Core\App;

/**
 * Information about an extension.
 * @property-read string $canonicalName Canonical (i.e. directory) name of
 * extension.
 * @property-read bool $enabled Whether or not extension is enabled..
 * @property-read string[] $loadAfter Names of extensions to load before this one.
 * @property-read string[] $dependencies List of extension dependencies along
 * with version constraints.
 * @property-read string[] $phpDependencies List of PHP dependencies.
 * @property-read string $appName App depedency.
 * @property-read string $appVersion App version constraints.
 * @property-read string $pKey Path key for location of extension.
 * @property-read string[] Names of imported extensions that depend on this one.
 */
class ExtensionInfo implements BasicRecord {
  /**
   * @var string Kind of extension, used for looking up location, e.g. "themes".
   */
  protected $kind = 'extensions';
  
  /**
   * @var string Canonical name.
   */
  private $canonicalName;
  
  /**
   * @var bool Whether or not extension is enabled.
   */
  private $enabled;
  
  /**
   * @var array Extension info.
   */
  private $info;
  
  /**
   * @var string
   */
  private $pKey;
  
  /**
   * @var string[]
   */
  private $loadAfter = array();
  
  /**
   * @var string[]
   */
  private $dependencies = array();
  
  /**
   * @var string[]
   */
  private $phpDependencies = array();
  
  /**
   * $var string
   */
  private $appName = null;

  /**
   * $var string
   */
  private $appVersion = null;
  
  /**
   * @var string[]
   */
  private $requiredBy = array();
  
  /**
   * Construct extension information.
   * @param string $canonicalName Canonical (i.e. directory) name of extension.
   * @param array $info Content of "extension.json" as an associative array.
   * @param string $pKey Library of extension as a path key.
   * @param bool $enabled Whether or not extension is enabled.
   */
  public function __construct($canonicalName, $info, $pKey = null, $enabled = true) {
    $this->canonicalName = $canonicalName;
    $this->info = $info;
    $this->pKey = $pKey;
    $this->enabled = $enabled;
    
    if (isset($info['dependencies'])) {
      foreach ($info['dependencies'] as $key => $value) {
        switch ($key) {
          case 'extensions':
            foreach ($value as $extension) {
              $this->dependencies[] = $extension;
              preg_match('/^ *([^ <>=!]+) *(.*)$/', $extension, $matches);
              $this->loadAfter[] = $matches[1];
            }
            break;
          case 'php':
            foreach ($value as $phpExtension)
              $this->phpDependencies[] = $phpExtension;
            break;
          default:
            $this->appName = $key;
            $this->appVersion = $value;
            break;
        }
      }
    }
    $this->loadAfter = array_unique($this->loadAfter);
  }
  
  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'canonicalName':
      case 'enabled':
      case 'loadAfter':
      case 'dependencies':
      case 'phpDependencies':
      case 'appName':
      case 'appVersion':
      case 'pKey':
      case 'requiredBy':
        return $this->$property;
    }
    return $this->info[$property];
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($property) {
    switch ($property) {
      case 'canonicalName':
      case 'enabled':
      case 'loadAfter':
      case 'dependencies':
      case 'phpDependencies':
      case 'appName':
      case 'appVersion':
      case 'pKey':
        return isset($this->$property);
    }
    return isset($this->info[$property]);
  }
  
  /**
   * Set the value of a property.
   * @param string $property Property.
   * @param mixed $value Value.
   */
  public function __set($property, $value) {
    $this->info[$property] = $value;
  }
  
  /**
   * Add an extension that requires this one.
   * @param string $extension Extension.
   */
  public function requiredBy($extension) {
    $this->requiredBy[] = $extension;
  }
  
  /**
   * Whether the extension is marked as a library. Libraries cannot be enabled
   * but will be automatically loaded when needed.
   * @return bool True if library.
   */
  public function isLibrary() {
    return isset($this->info['library']) and $this->info['library'];
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->info;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function getModel() {
    return ExtensionModel::getInstance();
  }
  
  /**
   * Get path key and path as a 2-tuple.
   * @param string $path Optional path.
   * @return string[] 2-tuple of key and path.
   */
  public function getKeyPath($path = '') {
    if ($this->pKey)
      return array($this->pKey, $this->kind . '/' . $this->canonicalName . '/' . $path);
    else
      return array($this->kind, $this->canonicalName . '/' . $path);
  }
  
  /**
   * Get a path relative to this extension's directory.
   * @param App $app Application.
   * @param string $path A relative path.
   * @return string An absolute path.
   */
  public function p(App $app, $path) {
    list($key, $path) = $this->getKeyPath($path);
    return $app->p($key, $path);
  }
  
  /**
   * Get an asset relative to this extension's directory.
   * @param Assets $assets Assets module.
   * @param string $path A relative path.
   * @return string An asset path.
   */
  public function getAsset(Assets $assets, $path) {
    list($key, $path) = $this->getKeyPath($path);
    return $assets->getAsset($key, $path);
  }
  
  /**
   * Add a subdirectory of this extension as an asset dir.
   * @param Assets $assets Assets module.
   * @param string $path A relative path.
   */
  public function addAssetDir(Assets $assets, $path) {
    list($key, $path) = $this->getKeyPath($path);
    $assets->addAssetDir($key, $path);
  }
  
  /**
   * Replaces variables in extension settings.
   * @param string[] $matches Matches.
   * @return string Value.
   */
  private function replaceVariable($matches) {
    if (isset($this->info[$matches[1]]))
      return $this->info[$matches[1]];
    return $matches[0];
  }
  
  /**
   * Replace PHP-style variables in a string if they correspond to keys in this
   * extension's settings.
   * @param string $string String containing variables.
   * @return string String with known variables replaced.
   */
  public function replaceVariables($string) {
    return preg_replace_callback(
      '/\$([a-z0-9]+)/i',
      array($this, 'replaceVariable'),
      $string
    );
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($field) {
    return $this->__isset($field);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($field) {
    return $this->__get($field);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($field, $value) {
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($field) {
  }
}