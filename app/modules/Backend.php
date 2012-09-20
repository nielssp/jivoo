<?php
// Module
// Name           : Backend
// Version        : 0.2.0
// Description    : The PeanutCMS administration system
// Author         : PeanutCMS
// Dependencies   : Database Authentication Errors Configuration Routes
//                  Templates Http

/**
 * PeanutCMS backend
 *
 * @package PeanutCMS
 */

/**
 * Backend class
 */
class Backend extends ModuleBase implements ILinkable, arrayaccess {

  private $categories = array();
  private $unlisted = null;

  private $controller;

  private $shortcuts = array();
  
  private $prefix = '';

  protected function init() {
    $this->m->Configuration->setDefault('backend.path', 'admin');

    $path = $this->m->Configuration->get('backend.path');
    $this->prefix = $path . '/';
    
    $this->controller = new BackendController(
      $this->m->Routes, $this->m->Configuration['backend']
    );
    
    $this->controller->addRoute($path, 'dashboard');
    $this->controller->addRoute($this->prefix . 'login', 'login');
    $this->controller->addRoute($this->prefix . 'access-denied', 'accessDenied');
    $this->controller->addRoute($this->prefix . 'about', 'about');

    $this['peanutcms']->setup('PeanutCMS', -2);
    $this['peanutcms']['home']->setup(tr('Home'), 0, null);
    $this['peanutcms']['dashboard']->setup(tr('Dashboard'), 0, array('path' => explode('/', $path)));
    $this['peanutcms']['about']->setup(tr('About'), 8)->autoRoute($this->controller, 'about');
    $this['peanutcms']['logout']->setup(tr('Log out'), 10)->autoRoute($this->controller, 'logout');
    
    $this['settings']->setup(tr('Settings'), 10);
    $mainConfigPage = new ConfigurationPage($this, $this->m->Templates);
    //    $this->addPage('settings', 'configuration', tr('Configuration'), array($mainConfigPage, 'controller'), 10);
    $this['settings']['configuration']->setup(tr('Configuration'), 10)->autoRoute($this->controller, 'configuration');
    $this['settings']['themes']->setup(tr('Themes'), 2);
    $this['settings']['modules']->setup(tr('Modules'), 2);
    
    if ($this->m->Authentication->hasPermission('backend.access')) {
      $this->m->Templates->set('notifications', LocalNotification::all());
    }
    if (!$this->m->Templates->hideIdentity()
        OR $this->m->Authentication->hasPermission('backend.access')) {
      $this->m->Templates->set(
        'aboutLink',
        $this->m->Routes->getLink(array('controller' => 'Backend', 'action' => 'about')),
        'backend/footer.html'
      );
    }
    if (!$this->m->Templates->hideVersion() OR $this->m->Authentication->hasPermission('backend.access')) {
      $this->m->Templates->set('version', PEANUT_VERSION);
    }
    else {
      $this->m->Templates->set('version', '');
    }

    $this->m->Routes->onRendering(array($this, 'createMenu'));
  }

  public function __get($property) {
    switch ($property) {
      case 'unlisted':
        if (!isset($this->unlisted)) {
          $this->unlisted = new BackendCategory($this, $this->m->Authentication);
        }
        return $this->unlisted;
      case 'prefix':
        return $this->$property;
    }
  }

  public function getRoute() {
    return array(
      'path' => explode('/', $this->m->Configuration->get('backend.path'))
    );
  }

  /** @todo In case of overflow; combine remaining categories under one "More"-category */
  /** @todo actually... it should be handled in the theme... */
  public function createMenu($sender, $eventArgs) {
    if (!$this->m->Authentication->hasPermission('backend.access')) {
      $this->m->Templates->set('menu', array(), 'backend/header.html');
      return array();
    }
    $menu = array();
    foreach ($this->categories as $category) {
      if ($category->count() > 0) {
        $category->group();
        $menu[] = $category;
      }
    }
    groupObjects($menu);
    $this->m->Templates->set('menu', $menu, 'backend/header.html');
    return $menu;
  }
  
  public function offsetExists($category) {
    return isset($this->categories[$category]);
  }
  
  public function offsetGet($category) {
    if (!isset($this->categories[$category])) {
      $this->categories[$category] = new BackendCategory($this, $this->m->Authentication);
    }
    return $this->categories[$category];
  }
  
  public function offsetSet($category, $value) {
    // not implemented
  }
  
  public function offsetUnset($category) {
    unset($this->categories[$category]);
  }
}
