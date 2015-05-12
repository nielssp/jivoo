<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Models\IBasicRecord;
use Jivoo\Models\IBasicModel;

/**
 * Column.
 * @property string $label Column label.
 * @property string $field Model field name.
 * @property bool $primary Whether or not column is primary.
 * @property bool $default Whether or not column is default sort option.
 * @property bool $descending If the default sorting is descending.
 * @property callable $cellRenderer Cell renderer with parameters
 * ({@see IBasicRecord} $record, {@see IBasicModel} $model) and return type
 * string.
 */
class Column extends JtkObject {
  public function __construct($label, $field = null, $cellRenderer = null) {
    $this->label = $label;
    $this->field = $field;
    $this->primary = false;
    $this->default = false;
    $this->descending = false;
    $this->cellRenderer = $cellRenderer;
    if (!isset($this->cellRenderer)) {
      $this->cellRenderer = array($this, 'defaultCellRenderer');
    }
  }
  
  public function setDefault($descending = false) {
    $this->default = true;
    $this->descending = $descending;
  }

  public function defaultCellRenderer(DataTable $table, IBasicRecord $record) {
    $field = $this->field;
    $type = $table->model->getType($field)->type;
    $cell = null;
    switch ($type) {
      case DataType::DATE:
        $cell = fdate($record->$field);
        break;
      case DataType::DATETIME:
        $cell = ldate($record->$field);
        break;
      case DataType::BOOLEAN:
        $cell = $record->$field ? tr('Yes') : tr('No');
        break;
      default:
        $cell = h($record->$field);
        break;
    }
    if ($this->primary) {
      if ($record instanceof ILinkable)
        $cell = $this->Html->link($cell, $record);
    }
    return $cell;
  }
}
