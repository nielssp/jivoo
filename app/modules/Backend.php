<?php
// Module
// Name           : Backend
// Version        : 0.2.0
// Description    : The PeanutCMS administration system
// Author         : PeanutCMS
// Dependencies   : database users errors actions configuration routes
//                  templates http

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
  private $unlisted = NULL;

  private $controller;

  private $shortcuts = array();
  
  private $prefix = '';

  protected function init() {
    $this->m->Configuration->setDefault('backend.path', 'admin');

    $path = $this->m->Configuration->get('backend.path');
    $this->prefix = $path . '/';
    $aboutPath = $path . '/about';
    
    $this->controller = new BackendController($this->m->Templates, $this->m->Routes);
    
    // no no no
    $this->controller->addModule($this->m->Users);

    if ($this->m->Users->isLoggedIn()) {
      $this->controller->addRoute($path, 'dashboard');
      $this->m->Templates->set('notifications', LocalNotification::all());
    }
    else {
      $this->controller->addRoute($path, 'login');
    }
    if (!$this->m->Templates->hideIdentity() OR $this->m->Users->isLoggedIn()) {
      $this->controller->addRoute($aboutPath, 'about');
      $this->m->Templates->set('aboutLink', $this->m->Http->getLink(explode('/', $aboutPath)), 'backend/footer.html');
    }
    else {
      $this->controller->addRoute($aboutPath, 'login');
    }

    $this->m->Routes->onRendering(array($this, 'createMenu'));
    
    $this['peanutcms']->setup('PeanutCMS', -2);
    $this['peanutcms']['home']->setup(tr('Home'), 0, NULL);
    $this['peanutcms']['dashboard']->setup(tr('Dashboard'), 0, array('path' => explode('/', $path)));
    $this['peanutcms']['about']->setup(tr('About'), 8, array('path' => explode('/', $aboutPath)));
    $this['peanutcms']['logout']->setup(tr('Log out'), 10, array('query' => array('logout' => '')));

    $this['settings']->setup(tr('Settings'), 10);
    $mainConfigPage = new ConfigurationPage($this, $this->m->Templates);
//    $this->addPage('settings', 'configuration', tr('Configuration'), array($mainConfigPage, 'controller'), 10);
    $this['settings']['configuration']->setup(tr('Configuration'), 10);
    $this['settings']['themes']->setup(tr('Themes'), 2);
    $this['settings']['modules']->setup(tr('Modules'), 2);
  }

  public function __get($property) {
    switch ($property) {
      case 'unlisted':
        if (!isset($this->unlisted)) {
          $this->unlisted = new BackendCategory();
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
    foreach ($this->categories as $category) {
     $category->group();
    }
    groupObjects($this->categories);
    $this->m->Templates->set('menu', $this->categories, 'backend/header.html');
    return $this->categories;
  }
  
  public function offsetExists($category) {
    return isset($this->categories[$category]);
  }
  
  public function offsetGet($category) {
    if (!isset($this->categories[$category])) {
      $this->categories[$category] = new BackendCategory($this);
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
