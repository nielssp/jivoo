<?php
// Module
// Name           : Pages
// Version        : 0.2.0
// Description    : The PeanutCMS content page system
// Author         : PeanutCMS
// Dependencies   : ApakohPHP/Database ApakohPHP/Routing ApakohPHP/Templates
//                  ApakohPHP/Authentication ApakohPHP/Backend ApakohPHP/Editors

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
    $newInstall = false;

    $pagesSchema = new pagesSchema();

    $newInstall = $this->m
      ->Database
      ->migrate($pagesSchema) == 'new';

    $this->m
      ->Database
      ->pages
      ->setSchema($pagesSchema);

    Page::connect($this->m
      ->Database
      ->pages);

    if ($newInstall) {
      $page = Page::create();
      $page->title = 'About';
      $page->name = 'about';
      $page->content = '<p>';
      $page->content .= tr(
        'Welcome to PeanutCMS. This is a static page. You can use it to display important information.');
      $page->content .= '</p>';
      $page->date = time();
      $page->state = 'published';
      $page->save();
    }

    $pagesEncoder = new Encoder();
    $pagesEncoder->setAllowAll(true);
    Page::setEncoder('content', $pagesEncoder);

    $this->config->defaults = array(
      'editor' => array(
        'name' => 'TinymceEditor',
      ),
    );

    $this->controller = new PagesController($this->m->Routing, $this->config);

    $this->detectFancyPath();
    $this->m->Routing->addPath('Pages', 'view', array($this, 'getFancyPath'));

    $this->m->Backend['content']->setup(tr('Content'), 2);
    $this->m->Backend['content']['pages-add']
      ->setup(tr('New page'), 2)
      ->permission('backend.pages.add')
      ->autoRoute($this->controller, 'add');
    $this->m->Backend['content']['pages-manage']
      ->setup(tr('Manage pages'), 4)
      ->permission('backend.pages.manage')
      ->autoRoute($this->controller, 'manage');

    $this->m->Backend->unlisted['pages-edit']
      ->permission('backend.pages.edit')
      ->autoRoute($this->controller, 'edit');
    $this->m->Backend->unlisted['pages-delete']
      ->permission('backend.pages.delete')
      ->autoRoute($this->controller, 'delete');
  }

  private function detectFancyPath() {
    $path = $this->request->path;
    if (!is_array($path) OR count($path) < 1) {
      return;
    }
    $name = implode('/', $path);
    $page = Page::first(
      SelectQuery::create()->where('name = ?', $name)
    );
    if ($page === false) {
      return;
    }
    $page->addToCache();
    $this->controller
      ->setRoute('view', 6, array($page->id));
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
}
