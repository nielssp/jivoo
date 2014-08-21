<?php
class TableAction {
  public $label;
  public $route;
  public $icon;
  public $method;
  public $data;
  public $confirmation;

  public function __construct($label, $route, $icon = null, $data = array(), $method = 'post', $confirmation = null) {
    $this->label = $label;
    $this->route = $route;
    $this->icon = $icon;
    $this->method = $method;
    $this->data = $data;
    $this->confirmation = $confirmation;
  }
}