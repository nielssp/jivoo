<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

/**
 * An icon provider that uses the class-attribute to provide icons, e.g. when
 * icons are provided by a style sheet.
 */
class ClassIconProvider implements IIconProvider {
  /**
   * @var string
   */
  private $classPrefix;
  
  /**
   * @var string[] A custom mapping from icon identifiers to class names.
   */
  protected $mapping = array();
  
  /**
   * Construct icon provoider.
   * @param string $classPrefix Class prefix for icon classes.
   */
  public function __construct($classPrefix = 'icon-') {
    $this->classPrefix = $classPrefix;
  }

  /**
   * {@inheritdoc}
   */
  public function getIcon($icon, $size = 16) {
    if (isset($this->mapping[$icon]))
      $icon = $this->mapping[$icon];
    return '<span class="' . $this->classPrefix . $icon . '"></span>';
  }
}