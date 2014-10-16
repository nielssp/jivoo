<?php
class ConfigurationAdminController extends AdminController {

  public function index() {
    $this->title = tr('Configuration');
    $this->settings = new Form('settings', $this->app->config['site']);
    if ($this->request->hasValidData('settings')) {
      $this->app->config['site']['title'] = $this->request->data['settings']['title'];
      $this->app->config['site']['subtitle'] = $this->request->data['settings']['subtitle'];
      if ($this->app->config->save())
        return $this->refresh();
      else
        $this->session->flash->error = tr('Could not save configuration');
    }
    return $this->render();
  }
}
