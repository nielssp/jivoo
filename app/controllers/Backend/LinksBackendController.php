<?php

class LinksBackendController extends BackendController {

  protected $models = array('Link');
  
  public function menus() {
    $this->links = $this->Link->all();
    $this->render();
  }
}
