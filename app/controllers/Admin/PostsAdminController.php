<?php
class PostsAdminController extends AdminController {
  
  protected $models = array('Post');
  
  public function index() {
    return $this->render('admin/dashboard.html');
  }
  public function add() {
    $this->title = tr('Add post');
    if ($this->request->hasValidData('Post')) {
      $this->post = $this->Post->create($this->request->data['Post']);
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
    }
    return $this->render();
  }
  
  public function edit($postId) {
    $this->title = tr('Edit post');
    $this->post = $this->Post->find($postId);
    if ($this->request->hasValidData('Post')) {
      $this->post->addData($this->request->data['Post']);
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
    return $this->render('admin/posts/add.html');
  }
}