<?php

class Link extends ActiveRecord implements ILinkable {

  protected $fields = array(
    'menu' => 'Menu',
    'position' => 'Position',
    'type' => 'Type',
    'title' => 'Title',
    'path' => 'Path'
  );

  protected $defaults = array(
    'menu' => 'main',
    'position' => 0
  );
  
  public function getRoute() {
    switch ($this->type) {
      case 'remote':
        return $this->path;
      case 'home':
        return null;
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

  public function setRoute($route = null) {
    if (!isset($route)) {
      $this->path = '';
      $this->type = 'home';
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      $this->setRoute($route->getRoute());
    }
    else if (is_array($route)) {
      if (isset($route['path'])) {
        $this->path = implode('/', $route['path']);
        $this->type = 'path';
      }
      else if (isset($route['controller'])) {
        $this->type = 'action';
        $this->path = $route['controller'];
        if (isset($route['action'])) {
          $this->path .= '/' . $route['action'];
          if (isset($route['parameters'])) {
            $this->path .= '/' . implode('/', $route['parameters']);
          }
        }
      }
    }
    else if (is_string ($route)) {
      $this->path = $route;
      $this->type = 'remote';
    }
    else {
      throw new InvalidArgumentException(tr('Invalid route.'));
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

