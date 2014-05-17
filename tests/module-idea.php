<?php

error_reporting(E_ALL);
ini_set('show_errors', 'true');

// Base class for modules/controllers/models/helpers
abstract class Module {
  // supports events -[and behaviours]
  // no more filemeta on every load?
  
  protected $modules = array();
  

  private $e;
  
  public function __construct() {
    echo 'Load modules: ' . implode(', ', $this->modules);
  }
  
  protected function inheritElements($property) {
    $value = $this->$property;
    $parent = new ReflectionClass(get_parent_class($this));
    while ($parent->name != 'Module') {
      $defaults = $parent->getDefaultProperties();
      $value = array_merge($value, $defaults[$property]);
      $parent = $parent->getParentClass();
    }
    $this->$property = array_unique($value);
  }

}


abstract class Controller extends Module {
  
  public final function __construct() {
    $this->inheritElements('modules');
    parent::__construct();
  }
}

class AppController extends Controller {
  protected $modules = array('Core', 'Routing');
}

class PostsController extends AppController {
  protected $modules = array('Foo', 'Core');
}

new PostsController;

