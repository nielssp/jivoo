<?php

class PagesController extends AppController {

  protected $helpers = array('Html', 'Form', 'Pagination', 'Backend');

  protected $modules = array('Editors');

  protected $models = array('Page');

  public function view($page) {
    $this->page = $this->Page->find($page);
    if (!$this->page
      or !($this->page->published
        or $this->Auth->hasPermission('backend.pages.viewDraft'))) {
      return $this->notFound();
    }
    $this->title = $this->page->title;
    return $this->render();
  }

}
