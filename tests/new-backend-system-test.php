<?php
include '../app/essentials.php';


class Backend {
  private $categories = array();

  public function __get($category) {
    if (!isset($categories[$category])) {
      $this->categories[$category] = new BackendCategory();
    }
    return $this->categories[$category];
  }
}

echo '<pre>';



echo '</pre>';
