<?php
class ConfigureHighlight extends ExtensionController {
  
  protected $helpers = array('Form');
 
  public function configure() {
    $this->title = 'Syntax highlighting';
    $this->settings = new Form('settings', $this->config);
    $themes = array();
    if (is_dir($this->p('styles'))) {
      $files = scandir($this->p('styles'));
      foreach ($files as $file) {
        if (preg_match('/^(.+)\.css$/i', $file, $matches) === 1) {
          $themes[$matches[1]] = $matches[1];
        }
      }
    }
    $this->themes = $themes;
    if ($this->request->hasValidData('settings')) {
      $this->config['theme'] = $this->request->data['settings']['theme'];
      if ($this->config->save())
        $this->refresh();
      else
        $this->session->flash->error = tr('Unable to save configuration');
    }
    return $this->render();
  }
}
