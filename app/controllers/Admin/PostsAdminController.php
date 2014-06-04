<?php
class PostsAdminController extends AdminController {
  public function index() {
    return $this->render('admin/dashboard.html');
  }
  public function add() {
    return $this->render('admin/dashboard.html');
  }
}