<?php
// Module
// Name           : Templates
// Description    : The Apakoh Core template/view system
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Assets Core/Helpers

/**
 * Templats and views module
 * @package Core\Templates
 * @property-read View $view The current view
 */
class Templates extends ModuleBase {
  protected function init() {
    $this->config->defaults = array(
      'title' => $this->app->name,
      'subtitle' => 'Powered by ' . $this->app->name,
    );
    
    $this->view = new View($this, $this->m->Routing);
    $this->view->addTemplateDir($this->p('templates', ''), 4);
    $this->view->site = $this->config->getArray();
    $this->view->app = $this->app->appConfig;

    if (isset($this->config['meta'])) {
      $meta = $this->config->get('meta');
      if (is_array($meta)) {
        foreach ($meta as $name => $content) {
          $this->view->meta($name, $content);
          $this->insertMeta($name, $content);
        }
      }
    }
  }
  
  /**
   * Get value of property
   * @param string $property Property
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'view':
        return $this->$property;
    }
  }

  /**
   * Return a link to an asset
   * @param string $file File name
   * @return string Link
   */
  public function getAsset($file) {
    return $this->m->Assets->getAsset($file);
  }

}
