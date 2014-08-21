<?php
class BulkAction {
  public $label;
  public $route;
  public $icon;
  public $method;
  public $data;

  public function __construct($label, $route, $icon = null, $data = array(), $method = 'POST') {
    $this->label = $label;
    $this->route = $route;
    $this->icon = $icon;
    $this->method = $method;
    $this->data = $data;
  }
}