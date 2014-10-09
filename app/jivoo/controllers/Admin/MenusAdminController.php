<?php
class MenusAdminController extends AdminController {

  public function index() {
    $this->title = tr('Menus');
    return $this->render();
  }
}
