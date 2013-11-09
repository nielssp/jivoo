<?php

class PagesBackendController extends BackendController {

  protected $helpers = array('Html', 'Form', 'Pagination', 'Backend', 'Filtering', 'Bulk');

  protected $modules = array('Editors');

  protected $models = array('Page');

  public function before() {
    parent::before();
    $this->config = $this->config['Pages'];
    $this->Filtering->addSearchColumn('title');
    $this->Filtering->addSearchColumn('content');
    $this->Filtering->addFilterColumn('status');
    $this->Filtering->addFilterColumn('date');
  
    $this->Filtering->addPredefined(tr('Published'), 'status:published');
    $this->Filtering->addPredefined(tr('Draft'), 'status:draft');
  
    $this->Pagination->setLimit(10);
  
    $this->Bulk
    ->addUpdateAction('publish', tr('Publish'),
      array('status' => 'published')
    );
    $this->Bulk
    ->addUpdateAction('conceal', tr('Conceal'), array('status' => 'draft'));
  
    $this->Bulk->addDeleteAction('delete', tr('Delete'));
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
              $this->Page->getFieldLabel($field) . ': ' . $error
            );
        }
      }
    }
    else {
      $this->page = $this->Page->create();
    }
    $this->title = tr('New page');
    $this->render('backend/pages/edit.html');
  }

  public function index() {
    $this->Backend->requireAuth('backend.pages.index');

    $select = SelectQuery::create()->orderByDescending('date');

    $this->Filtering->filter($select);
    
    if (isset($this->request->query['filter'])) {
      $this->Pagination->setCount($this->Page->count($select));
    }
    else {
      $this->Pagination->setCount($this->Page->count());
    }
    
    if ($this->Bulk->isBulk()) {
      if ($this->Bulk->isDelete()) {
        $query = $this->Page->dataSource->select();
      }
      else {
        $query = $this->Page->dataSource->update();
      }
      $this->Filtering->filter($query);
      $this->Bulk->select($query);
      $query->execute();
      if (!$this->request->isAjax()) {
        $this->refresh();
      }
    }
    
    $this->Pagination->paginate($select);
    
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
    $this->render();
  }

  public function delete($page) {
    $this->Backend->requireAuth('backend.pages.delete');
    $this->render('not-implemented.html');
  }

}
