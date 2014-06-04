<?php
class IconMenuWidget extends Widget {
  
  protected $helpers = array('Widget', 'Icon');
  
  public function main($options) {
    $this->menu = $options['menu'];
    return $this->fetch();
  }
}