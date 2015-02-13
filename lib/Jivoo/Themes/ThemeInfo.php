<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Themes;

/**
 * Information about a theme including custom properties in the "theme.json"
 * file.
 * @property-read string[] $extend List of parent themes.
 * @property-read string[] $zones List of enabled zones.
 */
class ThemeInfo extends ExtensionInfo {
  /**
   * {@inheritdoc}
   */
  protected $kind = 'themes';

  /**
   * @var string[] List of parent themes.
   */
  private $extend = array();
  
  /**
   * @var string[] List of enabled zones.
   */
  private $zones = array();

  /**
   * Construct theme information.
   * @param string $canonicalName Canonical (i.e. directory) name of theme.
   * @param array $info Content of "theme.json" as an associative array.
   * @param string[] $zones List of zones that the theme is enabled for.
   * @param string $pKey Library of theme as a path key.
   */
  public function __construct($canonicalName, $info, $zones, $pKey = null) {
    parent::__construct($canonicalName, $info, $pKey, count($zones) > 0);
    if (isset($info['extend']) and is_array($info['extend']))
      $this->extend = $info['extend'];
    if (isset($info['zones']) and is_array($info['zones']))
      $this->zones = $info['zones'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getModel() {
    return ThemeModel::getInstance();
  }
  
  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'extend':
      case 'zones':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  /**
   * {@inheritdoc}
   */
  public function __isset($property) {
    switch ($property) {
      case 'extend':
      case 'zones':
        return true;
    }
    return parent::__isset($property);
  }
}