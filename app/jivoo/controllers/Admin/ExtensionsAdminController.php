<?php
class ExtensionsAdminController extends AdminController {

  protected $modules = array('Extensions');
  
  public function index() {
    $this->title = tr('Extensions');
    $this->model = ExtensionModel::getInstance();
    $this->extensions = $this->m->Extensions->listAllExtensions();
    return $this->render();
  }
  
  public function configure($extension) {
    $info = $this->m->Extensions->getInfo($extension);
    if (!isset($info)) {
      $this->session->flash->error = tr('Extension not found: %1', h($extension));
      return $this->redirect('index');
    }
    if (!isset($info->configure)) {
      $this->session->flash->error = tr('Extension has no configuration: %1', h($extension));
      return $this->redirect('index');
    }
    $component = $info->configure;
    Lib::assumeSubclassOf($component, 'ExtensionController');
    $controller = new $component($this->app, $info, $this->config['Extensions']['config'][$extension]);
    $this->component = $controller->configure();
    return $this->render();
  }
  
  public function enable($extension) {
    if ($this->request->hasValidData()) {
      $missing = $this->m->Extensions->enable($extension); 
      if ($missing === true) {
        if (!$this->config->save())
          $this->session->flash->error = tr('Unable to save configuration');
        else
          $this->session->flash->success = tr('Extension enabled');
      }
      else {
        $this->session->flash->error = tr('Dependencies missing');
      }
    }
    return $this->redirect('index');
  }
  
  public function disable($extension) {
    if ($this->request->hasValidData()) {
      $this->m->Extensions->disable($extension);
      if (!$this->config->save())
        $this->session->flash->error = tr('Unable to save configuration');
      else
        $this->session->flash->success = tr('Extension disabled');
    }
    return $this->redirect('index');
  }
}
