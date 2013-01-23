<?php

class BulkHelper extends ApplicationHelper {

  private $actions = array();
  private $action = null;
  private $delete = false;
  private $primaryKey = 'id';

  private $started = false;

  public function begin() {
    if ($this->started) {
      return '';
    }
    $this->started = true;
    $html = '<form action="' . $this->getLink(array())
        . '" id="bulk" method="post">' . PHP_EOL;
    $html .= '<input type="hidden" name="access_token" value="'
        . $this->request
          ->getToken() . '" />' . PHP_EOL;
    return $html;
  }

  public function end() {
    if (!$this->started) {
      return '';
    }
    $this->started = false;
    return '</form>';
  }

  public function isStarted() {
    return $this->started;
  }

  public function addDeleteAction($action, $label) {
    $this->actions[$action] = array('type' => 'delete', 'name' => $action,
      'label' => $label
    );
  }

  public function addUpdateAction($action, $label, $data) {
    $this->actions[$action] = array('type' => 'update', 'name' => $action,
      'label' => $label, 'data' => $data
    );
  }

  public function getActions() {
    return $this->actions;
  }

  public function isBulk() {
    if (isset($this->action)) {
      return true;
    }
    if (!$this->request
      ->isPost()) {
      return false;
    }
    if (!isset($this->request
      ->data['all'])) {
      if (!isset($this->request
        ->data['records'])) {
        return false;
      }
      else if (!is_array($this->request
        ->data['records'])) {
        return false;
      }
      else if (empty($this->request
        ->data['records'])) {
        return false;
      }
    }

    foreach ($this->actions as $action => $info) {
      if (isset($this->request
        ->data[$action])) {
        $this->action = $info;
        if ($info['type'] == 'delete') {
          $this->delete = true;
        }
        return true;
      }
    }
    return false;
  }

  public function isDelete() {
    return $this->isBulk() AND $this->delete;
  }

  public function isUpdate() {
    return $this->isBulk() AND !$this->delete;
  }

  public function select(ICondition $query) {
    if (!$this->isBulk()) {
      return;
    }
    if (!isset($this->request
      ->data['all']) OR $this->request
          ->data['all'] != 'all') {
      $records = $this->request
        ->data['records'];
      $where = new Condition();
      foreach ($records as $id => $value) {
        $where->or($this->primaryKey . ' = ?', $id);
      }
      $query->and($where);
    }
    if ($this->isUpdate() AND $query instanceof UpdateQuery) {
      $query->set($this->action['data']);
    }
  }

}
