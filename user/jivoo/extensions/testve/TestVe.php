<?php
class TestVe extends ExtensionModule implements IFormExtension {
  
  private $Form = null;
  
  public function prepare() {
    $this->Form = $this->view->data->Form;
    return true;
  }
  public function label($label = null, $attributes = array()) {
    return '<label>View extension</label>';
  }
  public function ifRequired($output) {
    return $output;
  }
  public function field($attributes = array()) {
    return '<input type="text" value="Hello, World!" />';
  }
  public function error($default = '') {
    return $default;
  }
}