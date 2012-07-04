<?php

class Link extends ActiveRecord implements ILinkable {
  
  public function getRoute() {
    switch ($this->type) {
      case 'remote':
        return $this->path;
      case 'home':
        return NULL;
      default:
        $path = explode('/', $this->path);
        if ($this->type == 'action') {
          $controller = array_shift($path);
          $action = array_shift($path);
          return array(
            'controller' => $controller,
            'action' => $action,
            'parameters' => $path
          );
        }
        return array('path' => $path);
    }
  }
  

  public static function getMenu($menu = 'main') {
    $menu = strtolower($menu);
    $select = SelectQuery::create()
      ->where('menu = ?')
      ->addVar($menu);
    return Link::all($select);
  }
}

