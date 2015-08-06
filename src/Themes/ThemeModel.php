<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Themes;

use Jivoo\Extensions\ExtensionModel;
use Jivoo\Models\DataType;

/**
 * Model for theme information. Can be used as a singleton, use
 * {@see getInstance()} to get instance.
 * @see ThemeInfo
 */
class ThemeModel extends ExtensionModel {
  /**
   * @var ThemeModel Singleton instance.
   */
  private static $instance = null;

  /**
   * Construct model.
   * @param string $name Name of model.
   */
  public function __construct($name = 'Theme') {
    parent::__construct($name);
    $this->addField('screenshot', tr('Screenshot'), DataType::string());
  }
  
  /**
   * Get singleton instance of model.
   * @return ThemeModel Model instance.
   */
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new ThemeModel();
    return self::$instance;
  }
}