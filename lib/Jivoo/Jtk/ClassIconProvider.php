<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

class ClassIconProvider implements IIconProvider {
  private $classPrefix;
  
  protected $mapping = array();
  
  public function __construct($classPrefix = 'icon-') {
    $this->classPrefix = $classPrefix;
  }
  
  public function getIcon($icon, $size = 16) {
    if (isset($this->mapping[$icon]))
      $icon = $this->mapping[$icon];
    return '<span class="' . $this->classPrefix . $icon . '"></span>';
  }
}