<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Models\IModel;

/**
 * Record meta data object.
 */
class Meta {
  /**
   * @var IModel Meta model.
   */
  private $model;

  /**
   * @var ActiveRecord
   */
  private $record;

  /**
   * @var string
   */
  private $recordKey;

  /**
   * @var mixed
   */
  private $id;

  /**
   * @var string[]
   */
  private $data = null;

  /**
   * @var string[]
   */
  private $changes = array();

  /**
   * @var bool[]
   */
  private $deletions = array();

  /**
   * Construct meta data object.
   * @param IModel $model Meta data model.
   * @param string $recordKey Name of key column in meta model (e.g. 'userId').
   * @param ActiveRecord $record Record data meta data describes.
   */
  public function __construct(IModel $model, $recordKey, ActiveRecord $record)  {
    $this->model = $model;
    $this->recordKey = $recordKey;
    $id = $record->getModel()->getAiPrimaryKey();
    $this->id = $record->$id;
    $this->record = $record;
  }

  /**
   * Get value of a variable.
   * @param string $variable Meta variable.
   * @return string Value.
   */
  public function __get($variable) {
    if (isset($this->deletions[$variable]))
      return null;
    if (array_key_exists($variable, $this->changes))
      return $this->changes[$variable];
    if (!isset($this->data))
      $this->fetch();
    if (!isset($this->data[$variable]))
      return null;
    return $this->data[$variable];
  }

  /**
   * Set value of a variable.
   * @param string $variable Meta variable.
   * @param string $value Meta value.
   */
  public function __set($variable, $value) {
    if (!isset($value)) {
      if (isset($this->data[$variable]))
        $this->deletions[$variable] = true;
      unset($this->changes[$variable]);
    }
    else {
      $this->changes[$variable] = $value;
      unset($this->deletions[$variable]);
    }
  }

  /**
   * Whether a variable is set.
   * @param string $variable Meta variable.
   * @return bool Whether variable exists.
   */
  public function __isset($variable) {
    if (isset($this->deletions[$variable]))
      return false;
    if (array_key_exists($variable, $this->changes))
      return isset($this->changes[$variable]);
    if (!isset($this->data))
      $this->fetch();
    return isset($this->data[$variable]);
  }

  /**
   * Unset a variable
   * @param string $variable Meta variable.
   */
  public function __unset($variable) {
    $this->__set($variable, null);
  }

  /**
   * Fetch variable values from model.
   * @param string[]|null $variables Optional list of variable names to fetch.
   */
  public function fetch($variables = null) {
    if ($this->record->isNew())
      return;
    $selection = $this->model->where('%c = %i', $this->recordKey, $this->id);
    if (isset($variables))
      $selection = $selection->and('variable IN %s()', $variables);
    if (!isset($this->data))
      $this->data = array();
    foreach ($selection->select(array('variable', 'value')) as $kv)
      $this->data[$kv['variable']] = $kv['value'];
  }

  /**
   * Save variables to model.
   */
  public function save() {
    if ($this->record->isNew())
      return;
    if (!empty($this->deletions)) {
      $this->model->where('%c = %i', $this->recordKey, $this->id)
        ->and('variable IN %s()', array_keys($this->deletions))
        ->delete();
      foreach ($this->deletions as $var => $val)
        unset($this->data[$var]);
      $this->deletions = array();
    }
    if (!empty($this->changes)) {
      $rows = array();
      foreach ($this->changes as $var => $val) {
        if (!isset($this->data[$var]) or $this->data[$var] != $val) {
          $this->data[$var] = $val;
          $rows[] = array(
            $this->recordKey => $this->id,
            'variable' => $var,
            'value' => $val
          );
        }
      }
      $this->model->insertMultiple($rows, true);
      $this->changes = array();
    }
  }
}
