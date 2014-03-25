<?php
// Module
// Name           : Pages
// Description    : The PeanutCMS content page system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Database Jivoo/Routing Jivoo/Templates Jivoo/Controllers
//                  Jivoo/Authentication PeanutCMS/Backend Jivoo/Editors
//                  Jivoo/Models

/**
 * Static pages
 * @package PeanutCMS\Pages
 */
class Pages extends ModuleBase {

  protected function init() {
    if ($this->m->Database->isNew('Page')) {
      $page = $this->m->Models->Page->create();
      $page->title = 'About';
      $page->name = 'about';
      $page->content = '<p>';
      $page->content .= tr(
        'Welcome to PeanutCMS. This is a static page. You can use it to display important information.');
      $page->content .= '</p>';
      $page->published = true;
      $page->save();
    }

    $pagesEncoder = new HtmlEncoder();
    $pagesEncoder->setAllowAll(true);
    $this->m->Models->Page->setEncoder('content', $pagesEncoder);

    $this->config->defaults = array(
      'editor' => array(
        'name' => 'TinymceEditor',
      ),
    );

//     $this->controller->setConfig($this->config);

    $this->detectFancyPath();
    $this->m->Routing->addPath('Pages', 'view', 1, array($this, 'getFancyPath'));

    $this->m->Routing->autoRoute('PagesBackend');
    
    $this->m->Backend['content']->setup(tr('Content'), 2)
      ->item(tr('New page'), 'Backend::Pages::add', 2, 'backend.pages.add')
      ->item(tr('Manage pages'), 'Backend::Pages', 4, 'backend.pages.index');
  }

  private function detectFancyPath() {
    $path = $this->request->path;
    if (!is_array($path) OR count($path) < 1) {
      return;
    }
    $name = implode('/', $path);
    $page = $this->m->Models->Page->where('name = ?', $name)->first();
    if (!isset($page)) {
      return;
    }
    $this->m->Routing->setRoute(array(
      'controller' => 'Pages',
      'action' => 'view',
      'parameters' => array($page->id)
    ), 6);
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
