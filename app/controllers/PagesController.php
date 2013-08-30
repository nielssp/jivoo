<?php

class PagesController extends AppController {

  protected $helpers = array('Html', 'Form', 'Pagination', 'Backend');

  protected $modules = array('Editors');

  protected $models = array('Page');

  public function view($page) {
    $this->page = $this->Page->find($page);
    if (!$this->page
      OR ($this->page->state != 'published'
        AND !$this->Auth->hasPermission('backend.pages.viewDraft'))) {
      return $this->notFound();
    }
    $this->title = $this->page->title;
    $this->render();
  }

}
