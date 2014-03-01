<?php
// app/controllers/AppController.php
class AppController extends Controller {
  public function index() {
    return 'Hello, World: ' . $this->request->accepts('xhtml');
  }
  public function test() {
    $this->method = $this->request->method;
    return $this->render();
  }
  public function notFound() {
    return $this->render();
  }
}
