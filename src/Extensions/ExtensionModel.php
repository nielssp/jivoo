<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Models\BasicModelBase;
use Jivoo\Models\DataType;

/**
 * Model for extension information. Can be used as a singleton, use
 * {@see getInstance()} to get instance.
 * @see ExtensionInfo
 */
class ExtensionModel extends BasicModelBase {
  /**
   * @var ExtensionModel Singleton instance.
   */
  private static $instance = null;
  
  /**
   * Construct model.
   * @param string $name Name of model.
   */
  public function __construct($name = 'Extension') {
    parent::__construct($name);
    $this->addField('canonicalName', tr('Canonical name'), DataType::string());
    $this->addField('name', tr('Name'), DataType::string());
    $this->addField('version', tr('Version'), DataType::string());
    $this->addField('description', tr('Description'), DataType::text());
    $this->addField('enabled', tr('Enabled'), DataType::boolean());
  }
  
  /**
   * Get singleton instance of model.
   * @return ExtensionModel Model instance.
   */
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new ExtensionModel();
    return self::$instance;
  }
}