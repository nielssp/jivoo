<?php

class PagesController extends ApplicationController {

  protected $helpers = array('Html', 'Form', 'Pagination');

  public function view($page) {
    $this->page = Page::find($page);
    if (!$this->page OR ($this->page->state != 'published'
        AND !$this->auth->hasPermission('backend.pages.viewDraft'))) {
      return $this->notFound();
    }
    $this->title = $this->page->title;
    $this->render();
  }
  
  public function add() {
    $this->beforePermalink = $this->m->Routes->getLink();

    Page::setFieldEditor(
      'content',
      $this->m->Editors->getEditor($this->config['editor'])
    );
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

  public function manage() {
    $select = SelectQuery::create()
      ->orderByDescending('date');
    
    if (isset($this->request->query['filter'])) {
      $this->filter = $this->request->query['filter'];
      $select->where('content LIKE ? OR title LIKE ?')
        ->addVar('%' . $this->filter . '%')
        ->addVar('%' . $this->filter . '%');
      $this->Pagination->setCount(Page::count(clone $select));
    }
    else {
      $this->Pagination->setCount(Page::count());
    }
    
    $this->Pagination->setLimit(10)->paginate($select);
    
    $this->pages = Page::all($select);
    $this->title = tr('Manage pages');
    $this->render();
  }
  
  public function edit($page) {
    $this->beforePermalink = $this->m->Routes->getLink();
    $this->page = Page::find($page);
    if (!$this->page) {
      return $this->notFound();
    }
    
    Page::setFieldEditor(
      'content',
      $this->m->Editors->getEditor($this->config['editor'])
    );
    
    if ($this->request->isPost()) {
      $this->page->addData($this->request->data['page']);
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
          new LocalNotice(tr('Page successfully saved'));
          $this->refresh();
        }
      }
      else {
        foreach ($this->page->getErrors() as $field => $error) {
          new LocalWarning($this->page->getFieldLabel($field) . ': ' . $error);
        }
      }
    }
    $this->title = tr('Edit page');
    $this->render('pages/edit.html');
  }

  public function delete($page) {
    $this->render('not-implemented.html');
  }
  
}
