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
class Backend extends ModuleBase implements ILinkable {

  private $controller;
  
  private $categories = array();

  private $shortcuts = array();

  protected function init() {
    $this->m->Configuration->setDefault('backend.path', 'admin');

    $path = $this->m->Configuration->get('backend.path');
    $aboutPath = $path . '/about';
    
    $this->controller = new BackendController($this->m->Templates, $this->m->Routes);
    
    $this->controller->addModule($this->m->Users);

    if ($this->m->Users->isLoggedIn()) {
      $this->controller->addRoute($path, 'dashboard');
      $this->m->Templates->addTemplateData('notifications', LocalNotification::all());
    }
    else {
      $this->controller->addRoute($path, 'login');
    }
    if (!$this->m->Templates->hideIdentity() OR $this->m->Users->isLoggedIn()) {
      $this->controller->addRoute($aboutPath, 'about');
      $this->m->Templates->addTemplateData('aboutLink', $this->m->Http->getLink(explode('/', $aboutPath)), 'backend/footer.html');
    }
    else {
      $this->controller->addRoute($aboutPath, 'login');
    }

    Hooks::attach('preRender', array($this, 'createMenu'));
    

    $this->addCategory('peanutcms', 'PeanutCMS', -2);
    $this->addLink('peanutcms', 'logout', tr('Log out'), $this->m->Actions->add('logout'), 10);
    $this->addLink('peanutcms', 'dashboard', tr('Dashboard'), explode('/', $path), 0);
    $this->addLink('peanutcms', 'about', tr('About'), explode('/', $aboutPath), 8);
    $this->addLink('peanutcms', 'home', tr('Home'), array(), 0);

    $this->addCategory('settings', tr('Settings'), 10);
    $mainConfigPage = new ConfigurationPage($this, $this->m->Templates);
    $this->addPage('settings', 'configuration', tr('Configuration'), array($mainConfigPage, 'controller'), 10);
    $this->addLink('settings', 'themes', tr('Themes'), array(), 2);
    $this->addLink('settings', 'modules', tr('Modules'), array(), 2);
  }

  public function getLink() {
    return array(
      'path' => explode('/', $this->m->Configuration->get('backend.path'))
    );
  }

  private function createShortcut($title, $category = NULL) {
    $titleArr = str_split($title);
    foreach ($titleArr as $char) {
      $shortcut = strtoupper($char);
      if (isset($this->shortcuts['root']) AND in_array($shortcut, $this->shortcuts['root'])) {
        continue;
      }
      if ($category == NULL AND in_array($shortcut, $this->shortcuts)) {
        continue;
      }
      if ($category != NULL) {
        if (isset($this->shortcuts[$category])
            AND is_array($this->shortcuts[$category])
            AND in_array($shortcut, $this->shortcuts[$category])) {
          continue;
        }
        if (!isset($this->shortcuts[$category]) OR
            !is_array($this->shortcuts[$category])) {
          $this->shortcuts[$category] = array();
        }
        $this->shortcuts[$category][] = $shortcut;
      }
      else {
        if (!isset($this->shortcuts['root']) OR !is_array($this->shortcuts['root'])) {
          $this->shortcuts['root'] = array();
        }
        $this->shortcuts['root'][] = $shortcut;
      }
      $this->shortcuts[] = $shortcut;
      return $shortcut;
    }
    return NULL;
  }

  public function addCategory($categoryId, $categoryTitle, $group = 0, $shortcut = NULL) {
    if (!isset($this->categories[$categoryId])) {
      $this->categories[$categoryId] = new BackendCategory();
    }
    $this->categories[$categoryId]->id = $categoryId;
    $this->categories[$categoryId]->title = $categoryTitle;
    $this->categories[$categoryId]->group = $group;
    if (!isset($this->categories[$categoryId]->shortcut)) {
      $this->categories[$categoryId]->shortcut = $this->createShortcut($categoryTitle);
    }
  }

  public function addLink($categoryId, $pageId, $pageTitle, $path, $group = 0, $shortcut = NULL) {
    if (!isset($this->categories[$categoryId])) {
      $this->addCategory($categoryId, ucfirst($categoryId));
    }
    if (!isset($this->categories[$categoryId]->links[$pageId])) {
      $this->categories[$categoryId]->links[$pageId] = new BackendLink();
    }
    $this->categories[$categoryId]->links[$pageId]->id = $pageId;
    $this->categories[$categoryId]->links[$pageId]->title = $pageTitle;
    $this->categories[$categoryId]->links[$pageId]->group = $group;
    $this->categories[$categoryId]->links[$pageId]->shortcut = $this->createShortcut($pageTitle, $categoryId);
    if (is_array($path)) {
      $this->categories[$categoryId]->links[$pageId]->path = $path;
      $this->categories[$categoryId]->links[$pageId]->link = $this->m->Http->getLink($path);
    }
    else {
      $this->categories[$categoryId]->links[$pageId]->link = $path;
    }
  }

  public function addPage($categoryId, $pageId, $pageTitle, $pageController, $group = 0, $shortcut = NULL) {
    $backend = $this->m->Configuration->get('backend.path');
    if ($this->m->Users->isLoggedIn()) {
      $this->m->Routes->addRoute($backend . '/' . $categoryId . '/' . $pageId, $pageController);
    }
    else {
      $this->controller->addRoute($backend . '/' . $categoryId . '/' . $pageId, 'login');
    }
    $path = array_merge(
      explode('/', $backend),
      explode('/', $categoryId),
      explode('/', $pageId)
    );
    if (!isset($this->categories[$categoryId])) {
      $this->addCategory($categoryId, ucfirst($categoryId));
    }
    if (!isset($this->categories[$categoryId]->links[$pageId])) {
      $this->categories[$categoryId]->links[$pageId] = new BackendLink();
    }
    $this->categories[$categoryId]->links[$pageId]->id = $pageId;
    $this->categories[$categoryId]->links[$pageId]->title = $pageTitle;
    $this->categories[$categoryId]->links[$pageId]->group = $group;
    $this->categories[$categoryId]->links[$pageId]->shortcut = $this->createShortcut($pageTitle, $categoryId);
    $this->categories[$categoryId]->links[$pageId]->path = $path;
    $this->categories[$categoryId]->links[$pageId]->link = $this->m->Http->getLink($path);
  }

  /** @todo In case of overflow; combine remaining categories under one "More"-category */
  /** @todo actually... it should be handled in the theme... */
  public function createMenu() {
    if (!$this->m->Users->isLoggedIn()) {
      return;
    }
    $menu = array();
    foreach ($this->categories as $category) {
      groupObjects($category->links);
      $menu[] = $category;
    }
    groupObjects($menu);
    $this->m->Templates->addTemplateData('menu', $menu, 'backend/header.html');
  }
}

class BackendCategory implements IGroupable {
  public $id;
  public $title;
  public $group;
  public $shortcut;

  public $links = array();

  public function getGroup() {
    return $this->group;
  }
}

class BackendLink implements IGroupable, ILinkable {
  public $id;
  public $title;
  public $group;
  public $path;
  public $link;
  public $shortcut;

  public function getGroup() {
    return $this->group;
  }

  public function getPath() {
    return $this->path;
  }

  public function getLink() {
    return $this->link;
  }
}
