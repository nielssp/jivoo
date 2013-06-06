<?php

class MenuHelper extends Helper {
  
  protected $models = array('Link');
  
  public function getMenu($menu = 'main') {
    $menu = strtolower($menu);
    $select = SelectQuery::create()
      ->where('menu = ?', $menu)
      ->orderBy('position');
    return $this->Link->all($select);
  }
}
