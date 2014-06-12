<?php
class RecordIndexAction {
  public $label;
  public $action;
  public $icon;

  public function __construct($label, $action, $icon = null) {
    $this->label = $label;
    $this->action = $action;
    $this->icon = $icon;
  }
}