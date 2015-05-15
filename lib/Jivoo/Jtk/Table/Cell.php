<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkObject;
use Jivoo\Jtk\JtkCollection;
use Jivoo\Models\IBasicRecord;

/**
 * A single tablle cell.
 * @property Row $row Associated row.
 * @property Column $column Associated column.
 * @property string $value Cell value.
 */
class Cell extends JtkObject {
  
  public function __construct(Row $row, Column $column, $value = '') {
    $this->row = $row;
    $this->column = $column;
    $this->value = $value;
  }
}
