<?php
class TagsAdminController extends AdminController {
  
  protected $models = array('Tag');
  
  public function before() {
    parent::before();
    $this->Filtering->addPrimary('tag');
  }
  
  public function index() {
    $this->title = tr('Tags');
    $this->tags = $this->Tag;
    return $this->render();
  }
  
  public function add() {
    $this->title = tr('Add tag');
    if ($this->request->hasValidData('Tag')) {
      $this->tag = $this->Tag->create($this->request->data['Tag']);
      if ($this->tag->save()) {
        $this->session->flash['success'][] = tr(
          'Tag saved.'
        );
        if (isset($this->request->data['save-close']))
          return $this->redirect('index');
        else if (isset($this->request->data['save-new']))
          return $this->refresh();
        return $this->redirect(array('action' => 'edit', $this->tag->id));
      }
    }
    else {
      $this->tag = $this->Tag->create();
    }
    return $this->render();
  }
  
  public function edit($tagIds = null) {
    $this->ContentAdmin->makeSelection($this->Tag, $tagIds);
    if (isset($this->ContentAdmin->selection)) {
      return $this->ContentAdmin
        ->editSelection()
        ->respond('index');
    }
    else {
      $this->title = tr('Edit tag');
      $this->tag = $this->ContentAdmin->record;
      if ($this->tag and $this->request->hasValidData('Tag')) {
        $this->tag->addData($this->request->data['Tag']);
        if ($this->tag->save()) {
          $this->session->flash['success'][] = tr(
            'Tag saved.'
          );
          if (isset($this->request->data['save-close']))
            return $this->redirect('index');
          else if (isset($this->request->data['save-new']))
            return $this->redirect('add');
          return $this->refresh();
        }
      }
      return $this->render('admin/tags/add.html');
    }
  }

  public function delete($tagIds = null) {
    return $this->ContentAdmin
      ->makeSelection($this->Tag, $tagIds)
      ->deleteSelection()
      ->confirm(tr('Delete the selected tags?'))
      ->respond('index');
  }
}
