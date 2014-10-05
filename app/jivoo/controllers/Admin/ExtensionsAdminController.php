<?php
class ExtensionsAdminController extends AdminController {

  protected $modules = array('Extensions');
  
  public function index() {
    $this->title = tr('Extensions');
    return $this->render();
  }
}
