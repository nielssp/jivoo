<?php
class ConfigureAnalytics extends ExtensionController {
  
  protected $helpers = array('Form');
 
  public function configure() {
    $this->title = 'Google Analytics';
    $this->settings = new Form('settings', $this->config);
    if ($this->request->hasValidData('settings')) {
      $this->config['id'] = $this->request->data['settings']['id'];
      if ($this->config->save())
        $this->refresh();
      else
        $this->session->flash->error = tr('Unable to save configuration');
    }
    return $this->render();
  }
}