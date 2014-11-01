<?php
class ThemesAdminController extends AdminController {

  protected $modules = array('Themes');
  
  public function index() {
    $this->title = tr('Themes');
    $this->model = ThemeModel::getInstance();
    $this->themes = $this->m->Themes->listThemes();
    return $this->render();
  }
}
