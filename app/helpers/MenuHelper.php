<?php

class MenuHelper extends Helper {
  
  protected $models = array('Link');
  
  public function getMenu($menu = 'main') {
    return $this->Link
      ->where('menu = %s', strtolower($menu))
      ->orderBy('position');
  }
}
