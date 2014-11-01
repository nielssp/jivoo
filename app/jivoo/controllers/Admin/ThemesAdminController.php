<?php
class ThemesAdminController extends AdminController {

  protected $modules = array('Themes');
  
  public function index() {
    $this->title = tr('Themes');
    $this->themes = $this->m->Themes->listThemes();
    return $this->render();
  }
}
