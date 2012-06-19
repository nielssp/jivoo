<?php

class PagesController extends ApplicationController {

  protected $helpers = array('Html');

  public function view($page) {
    $this->page = Page::find($page);
    $this->title = $this->page->title;
    $this->render();
  }
  
  public function add() {
    if ($this->Request->isPost()) {
    }
    $this->render();
  }
  
}
