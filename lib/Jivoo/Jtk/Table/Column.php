<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

/**
 * Column.
 * @property string $label Column label.
 * @property callback $cellRenderer Cell renderer.
 */
class Column extends JtkObject {
  public function __construct($label, $cellRenderer = null) {
    $this->label = $label;
    $this->cellRenderer = $cellRenderer;
    if (!isset($this->cellRenderer) {
      $this->cellRenderer = array($this, 'defaultCellRenderer');
    }
  }

  public function defaultCellRenderer(IBasicRecord $record) {
    
  }
}
