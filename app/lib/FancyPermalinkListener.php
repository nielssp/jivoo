<?php
class FancyPermalinkListener extends AppListener {
  
  protected $handlers = array('Routing.beforeRender');

  public function beforeRender() {
    echo 1;
    exit;
  }
}