<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Models\IBasicRecord;
use Jivoo\Assets\Assets;
use Jivoo\Core\App;

/**
 * Information about an extension.
 * @property-read string $canonicalName Canonical (i.e. directory) name of
 * extension.
 * @property-read bool $enabled Whether or not extension is enabled..
 */
class ExtensionInfo implements IBasicRecord {
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
   * @var string Path key for library.
   */
  private $pKey;
  
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
  }
  
  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'canonicalName':
      case 'enabled':
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
        return true;
    }
    return isset($this->info[$property]);
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
   * Get a path relative to this extension's directory.
   * @param App $app Application.
   * @param string $path A relative path.
   * @return string An absolute path.
   */
  public function p(App $app, $path) {
    if ($this->pKey)
      return $app->p($this->pKey, $this->kind . '/' . $this->canonicalName . '/' . $path);
    else
      return $app->p($this->kind, $this->canonicalName . '/' . $path);
  }
  
  /**
   * Get an asset relative to this extension's directory.
   * @param Assets $assets Assets module.
   * @param string $path A relative path.
   * @return string An asset path.
   */
  public function getAsset(Assets $assets, $path) {
    if ($this->pKey)
      return $assets->getAsset($this->pKey, $this->kind . '/' . $this->canonicalName . '/' . $path);
    else
      return $assets->getAsset($this->kind, $this->canonicalName . '/' . $path);
  }
  
  /**
   * Add a subdirectory of this extension as an asset dir.
   * @param Assets $assets Assets module.
   * @param string $path A relative path.
   */
  public function addAssetDir(Assets $assets, $path) {
    if ($this->pKey)
      $assets->addAssetDir($this->pKey, $this->kind . '/' . $this->canonicalName . '/' . $path);
    else
      $assets->addAssetDir($this->kind, $this->canonicalName . '/' . $path);
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