<?php
class MediaAdminController extends AdminController {

  public function index() {
    $this->title = tr('Media');
    return $this->render();
  }
}
