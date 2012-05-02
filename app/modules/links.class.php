<?php
/*
 * Menu system
 *
 * @package PeanutCMS
 */

/**
 * Links class
 */
class Links implements IModule{

  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;
  private $users;
  private $backend;

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getHttp() {
    return $this->http;
  }

  public function getDatabase() {
    return $this->database;
  }

  public function getUsers() {
    return $this->users;
  }

  public function getBackend() {
    return $this->backend;
  }

  public function getRoutes() {
    return $this->routes;
  }

  public function getTemplates() {
    return $this->templates;
  }

  public function __construct(Backend $backend) {
    $this->backend = $backend;
    $this->users = $this->backend->getUsers();
    $this->database = $this->backend->getDatabase();
    $this->routes = $this->database->getRoutes();
    $this->http = $this->routes->getHttp();
    $this->templates = $this->routes->getTemplates();
    $this->errors = $this->routes->getErrors();
    $this->configuration = $this->database->getConfiguration();

    if (!ActiveRecord::isConnected()) {
      throw new Exception('temporary.');
    }

    $newInstall = FALSE;

    require_once(p(MODELS . 'link.class.php'));

    if (!$this->database->tableExists('links')) {
      $this->database->createQuery('links')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('menu', 255)
        ->addVarchar('type', 10)
        ->addVarchar('title', 255)
        ->addText('path')
        ->addIndex(FALSE, 'menu')
        ->execute();
      $newInstall = TRUE;
    }

    ActiveRecord::addModel('Link', 'links');

    if ($newInstall) {
      $link = Link::create();
      $link->menu = 'main';
      $link->type = 'home';
      $link->title = tr('Home');
      $link->path = '';
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->type = 'path';
      $link->title = tr('About');
      $link->path = 'about';
      $link->save();
    }
  }

  public function getPath(Link $record) {

  }

  public function getLink(Link $record) {
    return $this->http->getLink($record->getPath());
  }

  public static function getDependencies() {
    return array('backend');
  }

}
