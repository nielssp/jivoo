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
  }

  public function getLink() {
  }
}

Link::setModule($this);