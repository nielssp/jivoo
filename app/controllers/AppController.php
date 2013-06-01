<?php

class AppController extends Controller {
  protected $e = null;
  
  public function __construct(Routing $routing, AppConfig $config = null) {
    parent::__construct($routing, $config);
    $this->e = new Dictionary();
  }

  public function addExtension($object) {
    $class = get_class($object);
    $this->e->$class = $object;
  }
}