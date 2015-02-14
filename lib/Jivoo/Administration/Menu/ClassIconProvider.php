<?php
class ClassIconProvider implements IIconProvider {
  private $classPrefix;
  
  public function __construct($classPrefix = 'icon-') {
    $this->classPrefix = $classPrefix;
  }
  
  public function getIcon($icon, $size = 16) {
    return '<span class="' . $this->classPrefix . $icon . '"></span>';
  }
}