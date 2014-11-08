<?php
class ConfigureRecaptcha extends ExtensionController {
  
  protected $helpers = array('Form');
 
  public function configure() {
    $this->title = 'reCAPTCHA';
    $this->settings = new Form('settings', $this->config);
    if ($this->request->hasValidData('settings')) {
      $this->config['privateKey'] = $this->request->data['settings']['privateKey'];
      $this->config['publicKey'] = $this->request->data['settings']['publicKey'];
      if ($this->config->save())
        $this->refresh();
      else
        $this->session->flash->error = tr('Unable to save configuration');
    }
    return $this->render();
  }
}