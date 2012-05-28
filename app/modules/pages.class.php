<?php
// Module
// Name           : Pages
// Version        : 0.2.0
// Description    : The PeanutCMS content page system
// Author         : PeanutCMS
// Dependencies   : errors configuration database routes templates http
//                  users backend

/*
 * Static pages
 *
 * @package PeanutCMS
 */

/**
 * Pages class
 */
class Pages implements IModule{

  private $core;
  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;
  private $users;
  private $backend;

  private $page;

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

    require_once(p(MODELS . 'page.class.php'));

    if (!$this->database->tableExists('pages')) {
      $this->database->createQuery('pages')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('name', 255)
        ->addVarchar('title', 255)
        ->addText('content')
        ->addInt('date', TRUE)
        ->addVarchar('state', 10)
        ->addIndex(TRUE, 'name')
        ->addIndex(FALSE, 'date')
        ->execute();
      $newInstall = TRUE;
    }

    ActiveRecord::addModel('Page', 'pages');

    if ($newInstall) {
      $page = Page::create();
      $page->title = 'About';
      $page->name = 'about';
      $page->content = '<p>';
      $page->content .= tr('Welcome to PeanutCMS. This is a static page. You can use it to display important information.');
      $page->content .= '</p>';
      $page->date = time();
      $page->state = 'published';
      $page->save();
    }

    $this->detectFancyPermalinks();

    $this->backend->addCategory('content', tr('Content'), 2);
    $this->backend->addPage('content', 'new-page', tr('New Page'), array($this, 'addPageController'), 2);
    $this->backend->addPage('content', 'manage-pages', tr('Manage Pages'), array($this, 'addPageController'), 4);
  }

  private function detectFancyPermalinks() {
    $path = $this->routes->getPath();
    if (!is_array($path)) {
      return;
    }
    $name = implode('/', $path);
    $page = Page::first(
      SelectQuery::create()
        ->where('name = ?')
        ->addVar($name)
    );
    if ($page === FALSE) {
      return;
    }
    $page->addToCache();
    $this->page = $page->id;
    $this->routes->setRoute(array($this, 'pageController'), 6);
  }

  public function getLink(Page $record) {
    return $this->http->getLink($record->getPath());
  }

  public function pageController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();

    $templateData['page'] = Page::find($this->page);

    $templateData['title'] = $templateData['page']->title;

    $this->templates->renderTemplate('page.html', $templateData);
  }

  public function addPageController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['title'] = tr('New Page');
    $this->templates->renderTemplate('backend/edit-post.html', $templateData);
  }
}
