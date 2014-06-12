<?php
class UsersAdminController extends AdminController {
  
  protected $models = array('User');
  
  public function index() {
    $this->title = tr('All users');
    $this->users = $this->User;
    return $this->render();
  }
  public function add() {
    $this->title = tr('Add user');
    if ($this->request->hasValidData('User')) {
      $this->user = $this->User->create($this->request->data['User']);
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
      $this->post->commenting = $this->config['blog']['commentingDefault'];
    }
    return $this->render();
  }
  
  public function edit($postId) {
    $this->title = tr('Edit post');
    $this->post = $this->Post->find($postId);
    if ($this->post and $this->request->hasValidData('Post')) {
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
