<?php

if (!is_a($this, 'Links')) {
  exit('This model should be loaded from the Links module.');
}

class Link extends ActiveRecord implements ILinkable {

  private static $links;

  public static function setModule(Links $linksModule) {
    self::$links = $linksModule;
  }

  public function getPath() {
    if ($this->type == 'home') {
      return array();
    }
    else if ($this->type == 'path') {
      return explode('/', $this->path);
    }
  }

  public function getLink() {
    if ($this->type == 'remote') {
      return $this->path;
    }
    else {
      return self::$links->getLink($this);
    }
  }

  public function getMenu($menu = 'main') {
    $menu = strtolower($menu);
    $select = SelectQuery::create()
      ->where('menu = ?')
      ->addVar($menu);
    return Link::all($select);
  }
}

Link::setModule($this);
