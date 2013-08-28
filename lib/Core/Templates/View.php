<?php
/**
 * View for presenting output to user
 * @package Core\Templates
 */
class View extends ViewBase {
  protected function embed($_template, $_data = array()) {
    extract($_data, EXTR_SKIP);
    extract($this->data, EXTR_SKIP);
    require($this->findTemplate($_template . '.php'));
  }
}