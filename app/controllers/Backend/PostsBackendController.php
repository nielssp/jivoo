<?php
class PostsBackendController extends BackendController {
  protected $helpers = array('Html', 'Pagination', 'Form', 'Filtering',
    'Backend', 'Json', 'Bulk'
  );
  
  protected $modules = array('Editors');
  
  protected $models = array('Post', 'Comment', 'Tag');
  
  public function preRender() {
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
  

  public function index() {
    $this->Backend->requireAuth('backend.posts.index');
  
    $select = SelectQuery::create()->orderByDescending('date');
  
    $this->Filtering->filter($select);
  
    if (isset($this->request->query['filter'])) {
      $this->Pagination->setCount($this->Post->count($select));
    }
    else {
      $this->Pagination->setCount($this->Post->count());
    }
  
    if ($this->Bulk->isBulk()) {
      if ($this->Bulk->isDelete()) {
        $query = $this->Post->dataSource->select();
      }
      else {
        $query = $this->Post->dataSource->update();
      }
      $this->Filtering->filter($query);
      $this->Bulk->select($query);
      $query->execute();
      if (!$this->request->isAjax()) {
        $this->refresh();
      }
    }
  
    $this->Pagination->paginate($select);
  
    $this->posts = $this->Post->all($select);
    $this->title = tr('Manage posts');
    if ($this->request->isAjax()) {
      $html = '';
      foreach ($this->posts as $this->post) {
        $html .= $this->view->fetch('posts/post.html');
      }
      $this->Json->respond(array('html' => $html));
    }
    else {
      $this->returnToThis();
      $this->render();
    }
  }
  
  public function add() {
    $this->Backend->requireAuth('backend.posts.add');
  
    $examplePost = $this->Post->create();
    $examplePost->name = '%name%';
    $examplePost->date = time();
    $exampleLink = explode('%name%',
      $this->m->Routing->getLink($examplePost)
    );
    $examplePost = null;
    $this->nameInPermalink = count($exampleLink) >= 2;
    $this->beforePermalink = $exampleLink[0];
    $this->afterPermalink = $exampleLink[1];
  
    $this->Post->setFieldEditor('content',
      $this->m->Editors->getEditor($this->config['editor'])
    );
  
    if ($this->request->isPost()
    AND $this->request->checkToken('post')) {
      $this->post = $this->Post->create($this->request->data['post']);
      if (isset($this->request->data['publish'])) {
        $this->post->status = 'published';
      }
      else {
        $this->post->status = 'draft';
      }
      if ($this->post->isValid()) {
        $this->post->setUser($this->Auth->getUser());
        $this->post->save();
        if ($this->post->status == 'published') {
          $this->redirect($this->post);
        }
        else {
          $this->session->notice(tr('Post successfully created'));
          $this->refresh();
        }
      }
      else {
        foreach ($this->post->getErrors() as $field => $error) {
          $this->session
          ->alert(
            $this->Post->getFieldLabel($field) . ': ' . $error
          );
        }
      }
    }
    else {
      $this->post = $this->Post->create();
    }
    $this->title = tr('New post');
    $this->render('backend/posts/edit.html');
  }
  
  public function edit($post) {
    $this->Backend->requireAuth('backend.posts.edit');
  
    $this->post = $this->Post->find($post);
    if (!$this->post) {
      return $this->notFound();
    }
  
    $this->Post->setFieldEditor('content',
      $this->m->Editors->getEditor($this->config['editor'])
    );
  
    if ($this->request->isPost()) {
      $this->post->addData($this->request->data['post']);
      if (isset($this->request->data['publish'])) {
        $this->post->status = 'published';
      }
      else if (!isset($this->request->data['post']['status'])) {
        $this->post->status = 'draft';
      }
      if ($this->post->isValid()) {
        $this->post->save();
        if (!$this->request->isAjax()) {
          $this->goBack();
          if ($this->post->status == 'published') {
            $this->redirect($this->post);
          }
          else {
            $this->session->notice(tr('Post successfully saved'));
            $this->refresh();
          }
        }
      }
      else {
        foreach ($this->post->getErrors() as $field => $error) {
          $this->session
          ->alert(
            $this->post->getFieldLabel($field) . ': ' . $error
          );
        }
      }
    }
    $examplePost = $this->Post->create();
    $examplePost->name = '%name%';
    $examplePost->date = time();
    $exampleLink = explode('%name%',
      $this->m->Routing->getLink($examplePost)
    );
    $examplePost = null;
    $this->nameInPermalink = count($exampleLink) >= 2;
    $this->beforePermalink = $exampleLink[0];
    $this->afterPermalink = $exampleLink[1];
    $this->title = tr('Edit post');
    if (!$this->request->isAjax()) {
      $this->render();
    }
    else {
      $this->Json
      ->respond(array('html' => $this->view->fetch('posts/post.html')));
    }
  }
  
  public function delete($post) {
    $this->Backend->requireAuth('backend.posts.delete');
  
    $this->render('not-implemented.html');
  }
  
}