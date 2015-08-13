<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkObject;
use Jivoo\Jtk\JtkCollection;
use Jivoo\Models\BasicRecord;

/**
 * A single tablle cell.
 * @property Row $row Associated row.
 * @property Column $column Associated column.
 * @property string $value Cell value.
 */
class Cell extends JtkObject {
  /**
   * Constract table cell.
   * @param Row $row Associated row.
   * @param Column $column Associated column.
   * @param string $value Cell value.
   */
  public function __construct(Row $row, Column $column, $value = '') {
    $this->row = $row;
    $this->column = $column;
    $this->value = $value;
  }
}
