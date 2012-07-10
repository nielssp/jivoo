<?php
define('ALLOW_REDIRECT', FALSE);
define('DEBUG', TRUE);
include '../app/essentials.php';


class Backend implements arrayaccess {
  private $categories = array();
  
  public function offsetExists($category) {
    return isset($this->categories[$category]);
  }
  
  public function offsetGet($category) {
    if (!isset($this->categories[$category])) {
      $this->categories[$category] = new BackendCategory();
    }
    return $this->categories[$category];
  }
  
  public function offsetSet($category, $value) {
    if (is_null($category)) {
    }
    else {
    }
  }
  
  public function offsetUnset($category) {
    unset($this->categories[$category]);
  }
}

class BackendCategory implements arrayaccess {
  private $items = array();

  public function setLabel($label) {
  }
  
  public function offsetExists($item) {
    return isset($this->items[$item]);
  }
  
  public function offsetGet($item) {
    if (!isset($this->items[$item])) {
      $this->items[$item] = new BackendItem();
    }
    return $this->items[$item];
  }
  
  public function offsetSet($item, $value) {
    if (is_null($item)) {
    }
    else {
    }
  }
  
  public function offsetUnset($item) {
    unset($this->items[$item]);
  }
}

class BackendItem {
  public function setLabel($label) {
  }
  
  public function setRoute($route) {
    
  }
}

echo '<pre>';

$core = new Core();
$backend = new Backend();

$controller = new PostsController(
  $core->loadModule('Templates'),
  $core->loadModule('Routes')
);

$backend['content']->setLabel(tr('Content'));
$backend['content']['new-post']->setLabel(tr('New post'));
$backend['content']['new-post']->setRoute(array(
  'controller' => $controller,
  'action' => 'add'
));


echo '</pre>';
