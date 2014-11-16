<?php
class PostsAdminController extends AdminController {
  
  protected $models = array('Post');
  
  public function before() {
    parent::before();
    $this->Filtering->addPrimary('title');
    $this->Filtering->addPrimary('contentText');
    $this->Editor->set($this->Post, 'content', new TextareaEditor('markdown'));
    $this->Format->enableExtensions($this->Post, 'content');
  }
  
  public function index() {
    $this->title = tr('Posts');
    $this->posts = $this->Post;
    return $this->render();
  }
  public function add() {
    $this->title = tr('Add post');
    if ($this->request->hasValidData('Post')) {
      $this->post = $this->Post->create($this->request->data['Post']);
      $this->post->user = $this->user;
      if ($this->post->status == 'published') {
        $this->post->published = time();
      }
      if ($this->post->save()) {
        $this->session->flash['success'][] = tr(
          'Post saved. %1',
          $this->Html->link(tr('Click here to view.'), $this->post)
        );
        if (isset($this->request->data['save-close']))
          return $this->redirect('index');
        else if (isset($this->request->data['save-new']))
          return $this->refresh();
        return $this->redirect(array('action' => 'edit', $this->post->id));
      }
    }
    else {
      $this->post = $this->Post->create();
      $this->post->createJsonTags();
      $this->post->commenting = $this->config['blog']['commentingDefault'];
    }
    $this->tags = Json::decode($this->post->jsonTags);
    return $this->render();
  }
  
  public function edit($postIds = null) {
    $this->ContentAdmin->makeSelection($this->Post, $postIds);
    if (isset($this->ContentAdmin->selection)) {
      return $this->ContentAdmin
        ->editSelection()
        ->respond('index');
    }
    else {
      $this->title = tr('Edit post');
      $this->post = $this->ContentAdmin->record;
      $this->post->createJsonTags();
      if ($this->post and $this->request->hasValidData('Post')) {
        $this->post->addData($this->request->data['Post']);
        if ($this->post->status == 'published' and empty($this->post->published)) {
          $this->post->published = time();
        }
        if ($this->post->save()) {
          $this->session->flash['success'][] = tr(
            'Post saved. %1',
            $this->Html->link(tr('Click here to view.'), $this->post)
          );
          if (isset($this->request->data['save-close']))
            return $this->redirect('index');
          else if (isset($this->request->data['save-new']))
            return $this->redirect('add');
          return $this->refresh();
        }
      }
      $this->tags = Json::decode($this->post->jsonTags);
      return $this->render('admin/posts/add.html');
    }
  }

  public function delete($postIds = null) {
    return $this->ContentAdmin
      ->makeSelection($this->Post, $postIds)
      ->deleteSelection()
      ->confirm(tr('Delete the selected posts?'))
      ->respond('index');
  }
}
