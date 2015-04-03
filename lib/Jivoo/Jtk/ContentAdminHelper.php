<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Helpers\Helper;

class ContentAdminHelper extends Helper {

  protected $helpers = array('Filtering');
  
  private $modelName = null;
  
  private $record = null;
  private $selection = null;
  
  private $cancel = false;
  private $confirmation = null;
  
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
  
  
  public function editSelection() {
    if (isset($this->selection)) {
      if ($this->request->hasValidData($this->modelName)) {
        $this->selection->set($this->request->data[$this->modelName])->update();
      }
    }
    return $this;
  }
  
  public function deleteSelection() {
    if (isset($this->request->data['cancel'])) {
      return $this;
    }
    if (isset($this->selection)) {
      if ($this->request->hasValidData()) {
        $this->selection->delete();
      }
    }
    else {
      $record = $this->record;
      if ($record and $this->request->hasValidData()) {
        $record->delete();
      }
    }
    return $this;
  }
  
  public function confirm($confirmation) {
    $this->confirmation = $confirmation;
    return $this;
  }
  
  public function respond($returnRoute = null) {
    if ($this->request->hasValidData()) {
      if ($this->request->isAjax())
        return new TextResponse(200, 'text/json', '{}');
      return $this->m->Routing->redirect($returnRoute);
    }
    if ($this->request->isAjax())
      return new TextResponse(200, 'text/json', '{}');
    if (!isset($this->confirmation))
      $this->confirmation = tr('Are you sure?');
    $this->view->data->title = tr('Confirm');
    $this->view->data->confirmation = $this->confirmation;
    $this->view->data->selection = $this->selection;
    $this->view->data->record = $this->record;
    return new ViewResponse(200, $this->view, 'admin/confirm.html');
  }
  
  public function makeSelection(IModel $model, $ids, $idField = 'id') {
    $this->modelName = $model->getName();
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
    return $this;
  }
}