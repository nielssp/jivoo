<?php

class MenuHelper extends Helper {
  
  protected $models = array('Link');
  
  public function getMenu($menu = 'main') {
    $menu = strtolower($menu);
    return $this->Links
      ->where('menu = ?', $menu)
      ->orderBy('position');
  }
}
