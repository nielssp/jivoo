<?php

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Menu', 'Auth', 'Widgets');
  
  public function notFound() {
    $this->setStatus(404);
    return $this->render('404.html');
  }
}
