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
      $this->newUser = $this->User->create($this->request->data['User']);
      if ($this->newUser->save()) {
        $this->session->flash['success'][] = tr(
          'Post saved. %1',
          $this->Html->link(tr('Click here to view.'), $this->newUser)
        );
        if (isset($this->request->data['save-close']))
          return $this->redirect('index');
        else if (isset($this->request->data['save-new']))
          return $this->refresh();
        return $this->redirect(array('action' => 'edit', $this->newUser->id));
      }
    }
    else {
      $this->newUser = $this->User->create();
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
