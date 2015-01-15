<?php
class AppController extends Controller {
  
  protected $helpers = array('Html');

  public function index() {
    return $this->render();
  }
  
  public function notFound() {
    $this->setStatus(404);
    return $this->render();
  }
}
