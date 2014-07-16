<?php
class DataTableRowWidget extends Widget {
  protected $options = array(
    'id' => '',
    'cells' => array(),
  );
  
  public function main($options) {
    return $this->fetch($options);
  }
}

