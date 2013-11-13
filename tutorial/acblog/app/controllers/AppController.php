<?php
// app/controllers/AppController.php
class AppController extends Controller {
  public function index() {
    $this->render();
  }
  public function test() {
    $this->method = $this->request->method;
    $this->render();
  }
  public function notFound() {
    $this->render();
  }
}
