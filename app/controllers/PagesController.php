<?php

class PagesController extends AppController {

  protected $models = array('Page');

  public function view($page) {
    $this->Format->encoder($this->Page, 'content')->setAllowAll(true);
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
