<?php
// Module
// Name           : Pages
// Version        : 0.2.0
// Description    : The PeanutCMS content page system
// Author         : PeanutCMS
// Dependencies   : Errors Configuration Database Routes Templates Http
//                  Authentication Backend

/*
 * Static pages
 *
 * @package PeanutCMS
 */

/**
 * Pages class
 */
class Pages extends ModuleBase {

  private $controller;
  
  protected function init() {
    $newInstall = FALSE;


    $pagesSchema = new pagesSchema();

    $newInstall = $this->m->Database->migrate($pagesSchema) == 'new';

    $this->m->Database->pages->setSchema($pagesSchema);

    Page::connect($this->m->Database->pages);

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
    
    $this->controller = new PagesController($this->m->Templates, $this->m->Routes);

    $this->detectFancyPath();
    $this->m->Routes->addPath('Pages', 'view', array($this, 'getFancyPath'));

    $this->m->Backend['content']->setup(tr('Content'), 2);
    $this->m->Backend['content']['page-add']->setup(tr('New page'), 2)->autoRoute($this->controller, 'add');
    
    $this->m->Backend->unlisted['page-edit']->autoRoute($this->controller, 'edit');
    //$this->m->Backend->addPage('content', 'manage-pages', tr('Manage Pages'), array($this, 'addPageController'), 4);
  }

  private function detectFancyPath() {
    $path = $this->m->Http->getRequest()->path;
    if (!is_array($path) OR count($path) < 1) {
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
    $this->controller->setRoute('view', 6, array($page->id));
  }
  
  public function getFancyPath($parameters) {
    if (is_object($parameters) AND is_a($parameters, 'Page')) {
      $record = $parameters;
    }
    else {
      $record = Page::find($parameters[0]);
    }
    return explode('/', $record->name);
  }

  public function addPageController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['title'] = tr('New Page');
    $this->m->Templates->renderTemplate('backend/edit-post.html', $templateData);
  }
}
