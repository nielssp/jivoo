<?php
// Module
// Name           : Pages
// Description    : The PeanutCMS content page system
// Author         : apakoh.dk
// Dependencies   : Core/Database Core/Routing Core/Templates Core/Controllers
//                  Core/Authentication PeanutCMS/Backend Core/Editors
//                  Core/Models

/**
 * Static pages
 * @package PeanutCMS\Pages
 */
class Pages extends ModuleBase {

  private $controller;

  protected function init() {
    if ($this->m->Database->isNew('pages')) {
      $page = $this->m->Models->Page->create();
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
    $this->m->Models->Page->setEncoder('content', $pagesEncoder);

    $this->config->defaults = array(
      'editor' => array(
        'name' => 'TinymceEditor',
      ),
    );

    $this->controller = $this->m->Controllers->Pages;
    $this->controller->setConfig($this->config);

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
    $page = $this->m->Models->Page->first(
      SelectQuery::create()->where('name = ?', $name)
    );
    if ($page === false) {
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
      $record = $this->m->Models->Page->find($parameters[0]);
    }
    return explode('/', $record->name);
  }
}
