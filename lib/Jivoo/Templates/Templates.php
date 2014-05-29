<?php
// Module
// Name           : Templates
// Description    : The Jivoo template/view system
// Author         : apakoh.dk

/**
 * Templats and views module
 * @package Jivoo\Templates
 * @property-read View $view The current view
 */
class Templates extends LoadableModule {
  
  protected function init() {
    $this->config->defaults = array(
      'title' => $this->app->name,
      'subtitle' => 'Powered by ' . $this->app->name,
    );
  }
  
  public function createView() {
    $view = new View($this, $this->m->Routing);
    $view->addTemplateDir($this->p('templates', ''), 4);
    $view->site = $this->config->getArray();
    $view->app = $this->app->appConfig;
    
    if (isset($this->config['meta'])) {
      $meta = $this->config->get('meta');
      if (is_array($meta)) {
        foreach ($meta as $name => $content) {
          $view->meta($name, $content);
          $this->insertMeta($name, $content);
        }
      }
    }
    return $view;
  } 
  
  public function getView() {
    if (!isset($this->view))
      $this->view = $this->createView();
    return $this->view;
  }
  
  /**
   * Get value of property
   * @param string $property Property
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'view':
        return $this->getView();
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
