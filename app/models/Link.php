<?php

class Link extends ActiveModel {

  protected $labels = array(
    'menu' => 'Menu',
    'position' => 'Position',
    'type' => 'Type',
    'title' => 'Title',
    'path' => 'Path'
  );

  protected $virtual = array(
    'route'
  );

  protected $getters = array(
    'route' => 'getRoute'
  );

  protected $setters = array(
    'route' => 'setRoute'
  );

  protected $defaults = array('menu' => 'main', 'position' => 0);

  public function getRoute(ActiveRecord $record) {
    switch ($this->type) {
      case 'remote':
        return $record->path;
      case 'home':
        return null;
      default:
        $path = explode('/', $record->path);
        if ($record->type == 'action') {
          $controller = array_shift($path);
          $action = array_shift($path);
          return array('controller' => $controller, 'action' => $action,
            'parameters' => $path
          );
        }
        return array('path' => $path);
    }
  }

  public function setRoute(ActiveRecord $record, $route = null) {
    if (!isset($route)) {
      $record->path = '';
      $record->type = 'home';
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      $record->setRoute($route->getRoute());
    }
    else if (is_array($route)) {
      if (isset($route['path'])) {
        $record->path = implode('/', $route['path']);
        $record->type = 'path';
      }
      else if (isset($route['controller'])) {
        $record->type = 'action';
        $record->path = $route['controller'];
        if (isset($route['action'])) {
          $record->path .= '/' . $route['action'];
          if (isset($route['parameters'])) {
            $record->path .= '/' . implode('/', $route['parameters']);
          }
        }
      }
    }
    else if (is_string($route)) {
      $record->path = $route;
      $record->type = 'remote';
    }
    else {
      throw new InvalidArgumentException(tr('Invalid route.'));
    }
  }

  public function recordMoveToTop(ActiveRecord $record) {
    $link = $this->where('menu = %s', $record->menu)
      ->and('id != %i', $record->id)
      ->orderBy('position')
      ->first();
    if ($link) {
      $record->position = $link->position - 1;
      $record->save();
    }
  }

  public function recordMoveToBottom(ActiveRecord $record) {
    $link = $this->where('menu = %s', $record->menu)
      ->and('id != %i', $record->id)
      ->orderBy('position')
      ->last();
    if ($link) {
      $record->position = $link->position + 1;
      $record->save();
    }
  }

  public function recordMoveUp(ActiveRecord $record) {
    $link = $this->where('menu = ?', $record->menu)
      ->and('id != %i', $record->id)
      ->and('position <= %i', $record->position)
      ->orderBy('position')
      ->last();
    if ($link) {
      $link->position = $record->position;
      $record->position--;
      $link->save();
      $record->save();
    }
  }

  public function recordMoveDown(ActiveRecord $record) {
    $link = $this->where('menu = ?', $record->menu)
      ->and('id != %i', $record->id)
      ->and('position >= %i', $record->position)
      ->orderBy('position')
      ->first();
    if ($link) {
      $link->position = $record->position;
      $record->position++;
      $link->save();
      $record->save();
    }
  }
}

