<?php
class ExtensionsAdminController extends AdminController {

  protected $modules = array('Extensions');
  
  public function index() {
    $this->title = tr('Extensions');
    $this->extensions = $this->m->Extensions->listExtensions();
    return $this->render();
  }
  
  public function enable($extension) {
    if ($this->request->hasValidData()) {
      $missing = $this->m->Extensions->enable($extension); 
      if ($missing === true) {
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
      $this->session->flash->success = tr('Extension disabled');
    }
    return $this->redirect('index');
  }
}
