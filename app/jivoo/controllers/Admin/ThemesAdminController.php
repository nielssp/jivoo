<?php
class ThemesAdminController extends AdminController {

  protected $modules = array('Theme');
  
  public function index() {
    $this->title = tr('Themes');
    return $this->render();
  }
}
