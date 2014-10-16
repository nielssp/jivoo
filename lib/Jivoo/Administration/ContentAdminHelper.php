<?php
class ContentAdminHelper extends Helper {

  protected $helpers = array('Filtering');
  
  private $record = null;
  private $selection = null;
  
  public function __get($property) { 
    switch ($property) {
      case 'record':
      case 'selection':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function __isset($property) { 
    switch ($property) {
      case 'record':
      case 'selection':
        return isset($this->$property);
    }
    return parent::__isset($property);
  }
  
  public function quickEdit($options) {
    $record = $options['record'];
    if ($record and $this->request->hasValidData()) {
      foreach ($options['edit'] as $field => $value) {
        $record->$field = $value;
      }
      if ($record->save()) {
        return $this->m->Routing->refresh();
      }
      else {
        $this->session->flash['error'][] = tr(
          'Unable to perform operation.'
        );
      }
    }
    return $this->view->render('admin/confirm.html', $options);
  }
  
  public function delete($options) {
    $record = $options['record'];
    if ($record and $this->request->hasValidData()) {
      $record->delete();
      return $this->m->Routing->refresh();
    }
    return $this->view->render('admin/confirm.html', $options);
  }
  
  public function makeSelection(IModel $model, $ids, $idField = 'id') {
    $this->record = null;
    $this->selection = null;
    if (isset($ids)) {
      $ids = explode(',', $ids);
      if (count($ids) == 1) {
        $this->record = $model->where($idField . ' = ?', $ids[0])->first();
      }
      else if (count($ids) > 1) {
        $this->selection = $model->where($idField . ' IN ?()', $ids);
      }
    }
    else if (isset($this->request->query['filter'])) {
      $this->selection = $this->Filtering->apply($model);
    }
  }
}