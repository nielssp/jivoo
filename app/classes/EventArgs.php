<?php

class EventArgs {
  public function __construct() {
    $properties = get_object_vars($this);
    $args = func_get_args();
    $argc = func_num_args();
    $argi = 0;
    foreach ($properties as $property => $value) {
      if ($argi >= $argc) {
        break;
      }
      if (isset($args[$argi])) {
        $this->$property = $args[$argi];
      }
      $argi++;
    }
  }

  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
  }
}
