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
      $this->m->Extensions->enable($extension);
    }
    return $this->redirect('index');
  }
  
  public function disable($extension) {
    if ($this->request->hasValidData()) {
      $this->m->Extensions->disable($extension);
    }
    return $this->redirect('index');
  }
}
