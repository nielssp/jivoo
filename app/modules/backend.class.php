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
class Backend implements IModule, ILinkable {

  private $core;
  private $database;
  private $users;
  private $errors;
  private $actions;
  private $configuration;
  private $routes;
  private $templates;
  private $http;

  private $categories = array();

  private $shortcuts = array();

  public function __construct(Core $core) {
    $this->core = $core;
    $this->database = $this->core->database;
    $this->actions = $this->core->actions;
    $this->routes = $this->core->routes;
    $this->http = $this->core->http;
    $this->templates = $this->core->templates;
    $this->errors = $this->core->errors;
    $this->configuration = $this->core->configuration;
    $this->users = $this->core->users;

    if (!$this->configuration->exists('backend.path')) {
      $this->configuration->set('backend.path', 'admin');
    }

    $path = $this->configuration->get('backend.path');
    $aboutPath = $path . '/about';

    if ($this->users->isLoggedIn()) {
      $this->routes->addRoute($path, array($this, 'dashboardController'));
    }
    else {
      $this->routes->addRoute($path, array($this, 'loginController'));
    }
    if (!$this->templates->hideIdentity() OR $this->users->isLoggedIn()) {
      $this->routes->addRoute($aboutPath, array($this, 'aboutController'));
      $this->templates->addTemplateData('aboutLink', $this->http->getLink(explode('/', $aboutPath)), 'backend/footer.html');
    }
    else {
      $this->routes->addRoute($aboutPath, array($this, 'loginController'));
    }

    Hooks::attach('preRender', array($this, 'createMenu'));

    $this->addCategory('peanutcms', 'PeanutCMS', -2);
    $this->addLink('peanutcms', 'logout', tr('Log out'), $this->actions->add('logout'), 10);
    $this->addLink('peanutcms', 'dashboard', tr('Dashboard'), explode('/', $path), 0);
    $this->addLink('peanutcms', 'about', tr('About'), explode('/', $aboutPath), 8);
    $this->addLink('peanutcms', 'home', tr('Home'), array(), 0);

    $this->addCategory('settings', tr('Settings'), 10);
    $mainConfigPage = new ConfigurationPage($this, $this->templates);
    $this->addPage('settings', 'configuration', tr('Configuration'), array($mainConfigPage, 'controller'), 10);
    $this->addLink('settings', 'themes', tr('Themes'), array(), 2);
    $this->addLink('settings', 'extensions', tr('Extensions'), array(), 2);
    $this->addLink('settings', 'modules', tr('Modules'), array(), 2);
  }

  public function getPath() {
    return explode('/', $this->configuration->get('backend.path'));
  }

  public function getLink() {
    $this->http->getLink($this->getPath());
  }

  private function createShortcut($title, $category = NULL) {
    $titleArr = str_split($title);
    foreach ($titleArr as $char) {
      $shortcut = strtoupper($char);
      if (in_array($shortcut, $this->shortcuts['root'])) {
        continue;
      }
      if ($category == NULL AND in_array($shortcut, $this->shortcuts)) {
        continue;
      }
      if ($category != NULL) {
        if (is_array($this->shortcuts[$category])
            AND in_array($shortcut, $this->shortcuts[$category])) {
          continue;
        }
        if (!is_array($this->shortcuts[$category])) {
          $this->shortcuts[$category] = array();
        }
        $this->shortcuts[$category][] = $shortcut;
      }
      else {
        if (!is_array($this->shortcuts['root'])) {
          $this->shortcuts['root'] = array();
        }
        $this->shortcuts['root'] = $shortcut;
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
      $this->categories[$categoryId]->links[$pageId]->link = $this->http->getLink($path);
    }
    else {
      $this->categories[$categoryId]->links[$pageId]->link = $path;
    }
  }

  public function addPage($categoryId, $pageId, $pageTitle, $pageController, $group = 0, $shortcut = NULL) {
    $backend = $this->configuration->get('backend.path');
    if ($this->users->isLoggedIn()) {
      $this->routes->addRoute($backend . '/' . $categoryId . '/' . $pageId, $pageController);
    }
    else {
      $this->routes->addRoute($backend . '/' . $categoryId . '/' . $pageId, array($this, 'loginController'));
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
    $this->categories[$categoryId]->links[$pageId]->link = $this->http->getLink($path);
  }

  /** @todo In case of overflow; combine remaining categories under one "More"-category */
  /** @todo actually... it should be handled in the theme... */
  public function createMenu() {
    if (!$this->users->isLoggedIn()) {
      return;
    }
    $menu = array();
    foreach ($this->categories as $category) {
      groupObjects($category->links);
      $menu[] = $category;
    }
    groupObjects($menu);
    $this->templates->addTemplateData('menu', $menu, 'backend/header.html');
  }

  public function dashboardController($path = array(), $parameters = array(), $contentType = 'html') {
    if (!$this->users->isLoggedIn()) {
      $this->loginController($path, $parameters, $contentType);
      return;
    }
    $templateData = array();

    $templateData['title'] = tr('Dashboard');

    $this->templates->renderTemplate('backend/dashboard.html', $templateData);
  }

  public function aboutController($path = array(), $parameters = array(), $contentType = 'html') {
    if (!$this->users->isLoggedIn() AND $this->templates->hideIdentity()) {
      $this->loginController($path, $parameters, $contentType);
      return;
    }
    $templateData = array();

    $templateData['title'] = tr('About');

    $this->templates->renderTemplate('backend/about.html', $templateData);
  }

  public function loginController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();

    $templateData['title'] = tr('Log in');
    $templateData['noHeader'] = TRUE;
    $templateData['loginAction'] = $this->actions->add('login');

    if ($this->actions->has('login')) {
      if ($this->users->logIn($_POST['login_username'], $_POST['login_password'])) {
        $this->http->refreshPath();
      }
      else {
        $templateData['loginError'] = TRUE;
        $templateData['loginUsername'] = htmlentities($_POST['login_username']);
      }
    }

    $this->templates->renderTemplate('backend/login.html', $templateData);
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
