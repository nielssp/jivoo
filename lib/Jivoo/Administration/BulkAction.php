<?php
class BulkAction {
  public $label;
  public $route;
  public $icon;

  public function __construct($label, $route, $icon = null) {
    $this->label = $label;
    $this->route = $route;
    $this->icon = $icon;
  }
}