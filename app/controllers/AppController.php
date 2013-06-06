<?php

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Menu');
  
  public function notFound() {
    $this->render('404.html');
  }
}