<?php
/*
 * PeanutCMS backend
 *
 * @package PeanutCMS
 */

/**
 * Backend class
 */
class Backend implements IModule, ILinkable {

  private $database;
  private $users;
  private $errors;
  private $actions;
  private $configuration;
  private $routes;
  private $templates;
  private $http;

  public function getDatabase() {
    return $this->database;
  }

  public function getUsers() {
    return $this->users;
  }

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getActions() {
    return $this->actions;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getHttp() {
    return $this->http;
  }

  public function getRoutes() {
    return $this->routes;
  }

  public function getTemplates() {
    return $this->templates;
  }

  public function __construct(Users $users) {
    $this->users = $users;
    $this->database = $this->users->getDatabase();
    $this->actions = $this->database->getActions();
    $this->routes = $this->database->getRoutes();
    $this->http = $this->routes->getHttp();
    $this->templates = $this->routes->getTemplates();
    $this->errors = $this->routes->getErrors();
    $this->configuration = $this->http->getConfiguration();

    if (!$this->configuration->exists('backend.path')) {
      $this->configuration->set('backend.path', 'admin');
    }

    $path = $this->configuration->get('backend.path');

    $this->routes->addRoute($path, array($this, 'dashboardController'));
  }

  public static function getDependencies() {
    return array('users');
  }

  public function getPath() {
    return explode('/', $this->configuration->get('backend.path'));
  }

  public function getLink() {
    $this->http->getLink($this->getPath());
  }

  public function addCategory($categoryId, $categoryTitle) {

  }

  public function addPage($categoryId, $pageId, $pageTitle, $pageController, $group = 0) {
    $backend = $this->configuration->get('backend.path');
    $this->routes->addRoute($backend . '/' . $categoryId . '/' . $pageId, $pageController);

  }

  public function dashboardController($parameters = array(), $contentType = 'html') {
    if (!$this->users->isLoggedIn()) {
      $this->loginController($parameters, $contentType);
      return;
    }
    $templateData = array();

    $templateData['title'] = tr('Dashboard');

    $this->templates->renderTemplate('backend/dashboard.html', $templateData);
  }

  public function loginController($parameters = array(), $contentType = 'html') {
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