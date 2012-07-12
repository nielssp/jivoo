<?php

class PagesController extends ApplicationController {

  protected $helpers = array('Html', 'Form');

  public function view($page) {
    $this->page = Page::find($page);
    $this->title = $this->page->title;
    $this->render();
  }
  
  public function add() {
    $this->beforePermalink = $this->m->Routes->getLink();
    if ($this->request->isPost()) {
      $this->page = Page::create($this->request->data['page']);
      if (isset($this->request->data['publish'])) {
        $this->page->state = 'published';
      }
      else {
        $this->page->state = 'draft';
      }
      if ($this->page->isValid()) {
        $this->page->save();
        if ($this->page->state == 'published') {
          $this->redirect($this->page);
        }
        else {
          new LocalNotice(tr('Page successfully created'));
          $this->refresh();
        }
      }
      else {
        foreach ($this->page->getErrors() as $field => $error) {
          new LocalWarning($this->page->getFieldLabel($field) . ': ' . $error);
        }
      }
    }
    else {
      $this->page = Page::create();
    }
    $this->title = tr('New page');
    $this->render('pages/edit.html');
  }
  
}
