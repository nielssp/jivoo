<?php
class IconMenuWidget extends Widget {
  
  protected $helpers = array('Widget', 'Icon');
  
  protected $options = array(
    'menu' => array(),
    'defaultAction' => '*',
    'defaultParameters' => '*'
  );
  
  public function main($options) {
    $this->menu = $options['menu'];
    if (!isset($this->menu))
      $this->menu = array();
    return $this->fetch();
  }
}