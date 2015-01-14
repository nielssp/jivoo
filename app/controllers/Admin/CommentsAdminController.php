<?php
class CommentsAdminController extends AdminController {
  
  protected $models = array('Comment');
  
  public function before() {
    parent::before();
    $this->Filtering->addPrimary('contentText');
    $this->Editor->set($this->Comment, 'content', new TextareaEditor('markdown'));
  }
  
  public function index() {
    $this->title = tr('All comments');
    $this->comments = $this->Comment;
    return $this->render();
  }
  public function add() {
    $this->title = tr('Add comment');
    if ($this->request->hasValidData('Comment')) {
      $this->comment = $this->Comment->create($this->request->data['Comment']);
      $this->comment->user = $this->user;
      if ($this->comment->save()) {
        $this->session->flash['success'][] = tr(
          'Comment saved. %1',
          $this->Html->link(tr('Click here to view.'), $this->comment)
        );
        if (isset($this->request->data['save-close']))
          return $this->redirect('index');
        else if (isset($this->request->data['save-new']))
          return $this->refresh();
        return $this->redirect(array('action' => 'edit', $this->comment->id));
      }
    }
    else {
      $this->comment = $this->Comment->create();
    }
    return $this->render();
  }
  
  public function edit($commentIds = null) {
    $this->ContentAdmin->makeSelection($this->Comment, $commentIds);
    if (isset($this->ContentAdmin->selection)) {
      return $this->ContentAdmin
        ->editSelection()
        ->respond('index');
    }
    else {
      $this->title = tr('Edit comment');
      $this->comment = $this->ContentAdmin->record;
      if ($this->comment and $this->request->hasValidData('Comment')) {
        $this->comment->addData($this->request->data['Comment']);
        if ($this->comment->save()) {
          $this->session->flash['success'][] = tr(
            'Comment saved. %1',
            $this->Html->link(tr('Click here to view.'), $this->comment)
          );
          if (isset($this->request->data['save-close']))
            return $this->redirect('index');
          else if (isset($this->request->data['save-new']))
            return $this->redirect('add');
          return $this->refresh();
        }
      }
      return $this->render('admin/comments/add.html');
    }
  }

  public function delete($commentIds = null) {
    return $this->ContentAdmin
      ->makeSelection($this->Comment, $commentIds)
      ->deleteSelection()
      ->confirm(tr('Delete the selected comments?'))
      ->respond('index');
  }
}
