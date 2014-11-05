<?php
class MenusAdminController extends AdminController {

  protected $models = array('Link');
  
  public function index() {
    $this->title = tr('Menus');
    $this->links = $this->Link;
    return $this->render();
  }
}
