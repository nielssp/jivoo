<?php

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Menu', 'Auth', 'Widgets');
  
  public function notFound() {
    $this->render('404.html');
  }
}