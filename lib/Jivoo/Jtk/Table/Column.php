<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Models\IBasicRecord;
use Jivoo\Models\IBasicModel;
use Jivoo\Jtk\JtkObject;
use Jivoo\Models\DataType;

/**
 * Table column.
 * @property string $label Column label.
 * @property string $field Model field name.
 * @property bool $primary Whether or not column is primary.
 * @property bool $default Whether or not column is default sort option.
 * @property bool $selected Whether or not column is the selected sort option.
 * @property bool $descending If the default sorting is descending.
 * @property int|string $size Optional width of column.
 * @property callable $cellRenderer Cell renderer with parameters
 * ({@see DataTable} $table, {@see IBasicRecord} $record) and return type
 * string.
 */
class Column extends JtkObject {
  /**
   * Construct table column.
   * @param string $label Column label
   * @param string $field Model field name.
   * @param callable $cellRenderer Cell renderer with parameters
   * ({@see DataTable} $table, {@see IBasicRecord} $record) and return type
   * string. 
   */
  public function __construct($label, $field = null, $cellRenderer = null) {
    $this->label = $label;
    $this->field = $field;
    $this->primary = false;
    $this->default = false;
    $this->selected = false;
    $this->descending = false;
    $this->cellRenderer = $cellRenderer;
    if (!isset($this->cellRenderer)) {
      $this->cellRenderer = array($this, 'defaultCellRenderer');
    }
  }
  
  /**
   * Set column as default sorting.
   * @param bool $descending Descending order.
   */
  public function setDefault($descending = false) {
    $this->default = true;
    $this->descending = $descending;
  }

  /**
   * Invoke cell renderer.
   * @param DataTable $table Table.
   * @param IBasicRecord $record Record.
   * @return string Value.
   */
  public function render(DataTable $table, IBasicRecord $record) {
    return call_user_func($this->cellRenderer, $table, $record);
  }

  /**
   * Default cell renderer.
   * @param DataTable $table Table.
   * @param IBasicRecord $record Record.
   * @return string Value.
   */
  public function defaultCellRenderer(DataTable $table, IBasicRecord $record) {
    $field = $this->field;
    $type = $table->model->getType($field)->type;
    $cell = null;
    switch ($type) {
      case DataType::DATE:
        $cell = '<em class="muted">' . fdate($record->$field) . '</em>';
        break;
      case DataType::DATETIME:
        $cell = '<em class="muted">' . ldate($record->$field) . '</em>';
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
