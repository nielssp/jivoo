<?php
class WidgetsAdminController extends AdminController {

  protected $modules = array('Widgets');
  
  public function index() {
    $this->title = tr('Widgets');
    $this->sidebar = new Form('sidebar', array(
      'sidebar' => Json::prettyPrint($this->config['Widgets']['areas']['sidebar']->getArray())
    ));
    if ($this->request->hasValidData('sidebar')) {
      $json = $this->request->data['sidebar']['sidebar'];
      $value = Json::decode($json);
      if (isset($value)) {
        $this->config['Widgets']['areas']['sidebar'] = $value;
        if ($this->app->config->save()) {
          $this->session->flash->success = tr('Sidebar saved');
          return $this->refresh();
        }
        else {
          $this->session->flash->error = tr('Could not save configuration');
        }
      }
      else {
        $this->session->flash->error = tr('Invalid JSON');
      }
    }
    return $this->render();
  }
}
