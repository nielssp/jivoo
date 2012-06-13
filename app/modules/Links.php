<?php
// Module
// Name           : Links
// Version        : 0.2.0
// Description    : The PeanutCMS graphical menu system
// Author         : PeanutCMS
// Dependencies   : errors configuration database routes templates http
//                  users backend

/*
 * Menu system
 *
 * @package PeanutCMS
 */

/**
 * Links class
 */
class Links implements IModule{

  private $core;
  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;
  private $users;
  private $backend;

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
    $this->backend = $this->core->backend;

    if (!ActiveRecord::isConnected()) {
      throw new Exception('temporary.');
    }

    $newInstall = FALSE;

    require_once(p(MODELS . 'Link.php'));

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

}
