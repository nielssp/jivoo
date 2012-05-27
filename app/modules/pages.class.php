<?php
/*
 * Static pages
 *
 * @package PeanutCMS
 */

/**
 * Pages class
 */
class Pages implements IModule{

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

  public function getBackend() {
    return $this->backend;
  }

  public function getUsers() {
    return $this->users;
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

  private $page;

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

  public static function getDependencies() {
    return array('backend');
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
