<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Helpers\Helper;
use Jivoo\Models\Model;
use Jivoo\Routing\TextResponse;
use Jivoo\View\ViewResponse;
use Jivoo\Routing\NotFoundException;

/**
 * A helper for typical administration tasks such as bulk edit and deletion.
 * @property-read Jivoo\Models\BasicRecord $record Selected record if any.
 * @property-read Jivoo\Models\Selection\BasicSelection|BasicRecord[] $selection
 * Selection or array of records if any.
 */
class ContentAdminHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Filtering');
  
  /**
   * @var string
   */
  private $modelName = null;
  
  /**
   * @var Model
   */
  private $record = null;
  
  /**
   * @var Jivoo\Models\Selection\BasicSelection
   */
  private $selection = null;
  
  /**
   * @var bool
   */
  private $cancel = false;
  
  /**
   * @var string|null
   */
  private $confirmation = null;
  
  /**
   * {@inheritdoc}
   */
  public function __get($property) { 
    switch ($property) {
      case 'record':
      case 'selection':
        return $this->$property;
    }
    return parent::__get($property);
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($property) { 
    switch ($property) {
      case 'record':
      case 'selection':
        return isset($this->$property);
    }
    return parent::__isset($property);
  }
  
  /**
   * Perform bulk edit.
   * @return self Self.
   */
  public function editSelection() {
    if (isset($this->selection)) {
      if ($this->request->hasValidData($this->modelName)) {
        $this->selection->set($this->request->data[$this->modelName])->update();
      }
    }
    return $this;
  }
  
  /**
   * Perform bulk deletion.
   * @return self Self.
   */
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
  
  /**
   * Set confirmation message.
   * @param string $confirmation Confirmation message.
   * @return self Self.
   */
  public function confirm($confirmation) {
    $this->confirmation = $confirmation;
    return $this;
  }
  
  /**
   * Respond with a confirmation dialog.
   * @property string|array|\Jivoo\Routing\Linkable|null $returnRoute A route,
   * see {@see Jivoo\Routing\Routing}.
   * @return \Jivoo\Routing\Response Response object.
   */
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
  
  /**
   * Make a selection based on request.
   * @param Model $model The model.
   * @param string|null $ids Optional comma-separated list of ids to select.
   * @param string $idField Name of id field.
   * @return self Self.
   */
  public function makeSelection(Model $model, $ids = null, $idField = 'id') {
    $this->modelName = $model->getName();
    $this->record = null;
    $this->selection = null;
    if (isset($ids)) {
      $ids = explode(',', $ids);
      $type = $model->getType($idField);
      if (count($ids) == 1) {
        $this->record = $model->where('%c = %_', $idField, $type, $ids[0])->first();
      }
      else if (count($ids) > 1) {
        $this->selection = $model->where('%c IN %_()', $idField, $type, $ids);
      }
    }
    else if (isset($this->request->query['filter'])) {
      $this->selection = $this->Filtering->apply($model);
    }
    return $this;
  }
}