<?php
/*
 * Static pages
 *
 * @package PeanutCMS
 */

/**
 * Pages class
 */
class Users implements IModule{

  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;

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

  public function getRoutes() {
    return $this->routes;
  }

  public function getTemplates() {
    return $this->templates;
  }

  public function __construct(Database $database) {
    $this->database = $database;
    $this->routes = $this->database->getRoutes();
    $this->http = $this->routes->getHttp();
    $this->templates = $this->routes->getTemplates();
    $this->errors = $this->routes->getErrors();
    $this->configuration = $this->database->getConfiguration();

    if (!ActiveRecord::isConnected()) {
      throw new Exception('temporary.');
    }

    $newInstall = FALSE;

    require_once(p(MODELS . 'user.class.php'));

    if (!$this->database->tableExists('users')) {
      $this->database->createQuery('users')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('username', 255)
        ->addVarchar('password', 255)
        ->addVarchar('email', 255)
        ->addInt('group_id', TRUE)
        ->addIndex(TRUE, 'username')
        ->addIndex(TRUE, 'email')
        ->execute();
      $newInstall = TRUE;
    }

    ActiveRecord::addModel('User', 'users');

  }

  public static function getDependencies() {
    return array('database');
  }

  public function getLink(User $record) {
    return $this->http->getLink($record->getPath());
  }
}