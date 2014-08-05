<?php
class ApplicationsController extends AppController {
  public function create() {
    $this->title = tr('Create application');
    return $this->render();
  }
  
  public function add() {
    $this->title = tr('Add application');
    if ($this->request->hasValidData()) {
      $path = $this->request->data['path'];
      if (file_exists($path)) {
        $app = include $path;
        $name = Utilities::stringToDashes($app['name']);
        $this->config['applications'][$name] = $path;
        $this->session->flash['success'][] = tr('Application "%1" successfully added.', $app['name']);
        $this->redirect(array('action' => 'dashboard', $name));
      }
      else {
        $this->session->flash['error'][] = tr('File does not exist.');
      }
    }
    return $this->render();
  }
  
  public function dashboard($name) {
    $this->app = new App($this->apps[$name]);
    $this->title = $this->app->name;
    return $this->render();
  }
}