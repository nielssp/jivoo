<?php
class GroupsAdminController extends AdminController {
  
  protected $models = array('Group');
  
  public function before() {
    parent::before();
    $this->Filtering->addPrimary('title');
  }
  
  public function index() {
    $this->title = tr('Groups');
    $this->groups = $this->Group;
    return $this->render();
  }
  public function add() {
    $this->title = tr('Add group');
    if ($this->request->hasValidData('Group')) {
      $this->group = $this->Group->create($this->request->data['Group']);
      if ($this->group->save()) {
        $this->session->flash['success'][] = tr(
          'Group saved.'
        );
        if (isset($this->request->data['save-close']))
          return $this->redirect('index');
        else if (isset($this->request->data['save-new']))
          return $this->refresh();
        return $this->redirect(array('action' => 'edit', $this->group->id));
      }
    }
    else {
      $this->group = $this->Group->create();
    }
    return $this->render();
  }
  
  public function edit($groupIds = null) {
    $this->ContentAdmin->makeSelection($this->Group, $groupIds);
    if (isset($this->ContentAdmin->selection)) {
      return $this->ContentAdmin
      ->editSelection()
      ->respond('index');
    }
    else {
      $this->title = tr('Edit group');
      $this->group = $this->ContentAdmin->record;
      if ($this->group and $this->request->hasValidData('Group')) {
        $this->group->addData($this->request->data['Group']);
        if ($this->group->save()) {
          $this->session->flash['success'][] = tr(
            'Group saved.'
          );
          if (isset($this->request->data['save-close']))
            return $this->redirect('index');
          else if (isset($this->request->data['save-new']))
            return $this->redirect('add');
          return $this->refresh();
        }
      }
      return $this->render('admin/groups/add.html');
    }
  }

  public function delete($groupIds = null) {
    return $this->ContentAdmin
      ->makeSelection($this->Group, $groupIds)
      ->deleteSelection()
      ->confirm(tr('Delete the selected groups?'))
      ->respond('index');
  }
}
