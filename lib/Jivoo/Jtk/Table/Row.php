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
 * A data table row.
 * @property DataTable $table Associated table.
 * @property IBasicRecord $record Associated record if any.
 * @property JtkCollection $cells Cells of row.
 * @property JtkCollection $actions Available row actions.
 */
class Row extends JtkObject {
  /**
   * Construct row.
   * @param DataTable $table Associated table.
   */
  public function __construct(DataTable $table) {
    $this->table = $table;
    $this->cells = new JtkCollection('Jivoo\Jtk\Table\Cell');
    $this->actions = clone $table->actions;
    foreach ($this->table->columns as $column)
      $this->cells->appendNew($this, $column);
  }
}
