<?php

class Event implements arrayaccess {
  private $functions = array();

  public function attach($function) {
    $this->functions[] = $function;
  }

  public function detach($function) {
    $key = array_search($function, $this->functions);
    if ($key !== false) {
      unset($this->functions[$key]);
    }
  }

  public function trigger($object, EventArgs $args) {
    foreach ($this->functions as $function) {
      call_user_func($function, $object, $args);
    }
  }

  public function offsetExists($offset) {
    return isset($this->functions[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->functions[$offset])
      ? $this->functions['offset'] : NULL;
  }

  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->attach($value);
    }
    else {
      $this->functions[$offset] = $value;
    }
  }

  public function offsetUnset($offset) {
    unset($this->functions[$offset]);
  }
}
