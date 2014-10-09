<?php
class ConfigurationAdminController extends AdminController {

  public function index() {
    $this->title = tr('Configuration');
    return $this->render();
  }
}
