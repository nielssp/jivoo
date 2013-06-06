<?php

class PagesController extends AppController {

  protected $helpers = array('Html', 'Form', 'Pagination', 'Backend');

  protected $modules = array('Editors');

  protected $models = array('Page');

  public function view($page) {
    $this->page = $this->Page->find($page);
    if (!$this->page
      OR ($this->page->state != 'published'
        AND !$this->auth->hasPermission('backend.pages.viewDraft'))) {
      return $this->notFound();
    }
    $this->title = $this->page->title;
    $this->render();
  }

  public function add() {
    $this->Backend->requireAuth('backend.pages.add');

    $this->beforePermalink = $this->m->Routing->getLink();

    $this->Page
      ->setFieldEditor('content',
        $this->m->Editors->getEditor($this->config['editor'])
      );
    if ($this->request->isPost()) {
      $this->page = $this->Page->create($this->request->data['page']);
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
          $this->session->notice(tr('Page successfully created'));
          $this->refresh();
        }
      }
      else {
        foreach ($this->page->getErrors() as $field => $error) {
          $this->session
            ->alert(
              $this->page->getFieldLabel($field) . ': ' . $error
            );
        }
      }
    }
    else {
      $this->page = $this->Page->create();
    }
    $this->title = tr('New page');
    $this->render('pages/edit.html');
  }

  public function manage() {
    $this->Backend->requireAuth('backend.pages.manage');

    $select = SelectQuery::create()->orderByDescending('date');

    if (isset($this->request->query['filter'])) {
      $this->filter = $this->request->query['filter'];
      $select->where('content LIKE ? OR title LIKE ?')
        ->addVar('%' . $this->filter . '%')->addVar('%' . $this->filter . '%');
      $this->Pagination->setCount($this->Page->count(clone $select));
    }
    else {
      $this->Pagination->setCount($this->Page->count());
    }

    $this->Pagination->setLimit(10)->paginate($select);

    $this->pages = $this->Page->all($select);
    $this->title = tr('Manage pages');
    $this->render();
  }

  public function edit($page) {
    $this->Backend->requireAuth('backend.pages.edit');

    $this->beforePermalink = $this->m->Routing->getLink();
    $this->page = $this->Page->find($page);
    if (!$this->page) {
      return $this->notFound();
    }

    $this->Page->setFieldEditor('content',
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
          $this->session->notice(tr('Page successfully saved'));
          $this->refresh();
        }
      }
      else {
        foreach ($this->page->getErrors() as $field => $error) {
          $this->session
            ->alert(
              $this->page->getFieldLabel($field) . ': ' . $error
            );
        }
      }
    }
    $this->title = tr('Edit page');
    $this->render('pages/edit.html');
  }

  public function delete($page) {
    $this->Backend->requireAuth('backend.pages.delete');
    $this->render('not-implemented.html');
  }

}
