<?php

class LinksBackendController extends BackendController {

  protected $models = array('Link');
  
  public function menu() {
    $this->links = $this->Link->all();
    $this->render();
  }
}
