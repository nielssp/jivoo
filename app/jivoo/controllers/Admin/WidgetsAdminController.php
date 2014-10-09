<?php
class WidgetsAdminController extends AdminController {

  protected $modules = array('Widgets');
  
  public function index() {
    $this->title = tr('Widgets');
    return $this->render();
  }
}
