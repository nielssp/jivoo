<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkObject;
use Jivoo\Jtk\JtkCollection;

/**
 * A data table.
 * @property Jivoo\Models\IBasicModel $model Model.
 * @property JtkCollection $columns Collection of {@see Column}s.
 * @property JtkCollection $sortOptions Collection of {@see Column}s.
 * @property JtkCollection $filters Collection of {@see Filter}s.
 * @property JtkCollection $actions Collection of row {@see Action}s.
 * @property JtkCollection $bulkActions Collection of bulk {@see Action}s.
 */
class DataTable extends JtkObject {
  
  public function __construct() {
    $this->columns = new JtkCollection('Jivoo\Jtk\Table\Column');
    $this->sortOptions = new JtkCollection('Jivoo\Jtk\Table\Column');
    $this->filters = new JtkCollection('Jivoo\Jtk\Table\Filter');
    $this->actions = new JtkCollection('Jivoo\Jtk\Table\Action');
    $this->bulkActions = new JtkCollection('Jivoo\Jtk\Table\Action');
  }
  
  /**
   * Automatically add columns from model.
   * @param string $field Field name.
   * @param string $fields,... Additional fields.
   */
  public function autoColumns($field) {
    assume(isset($this->model), tr('No model set'));
    $fields = func_get_args();
    foreach ($fields as $field) {
      $this->columns->append(
        new Column($this->model->getLabel($field)),
        $field
      );
    }
  }

  /**
   * Automatically add sort options from model.
   * @param string $field Field name.
   * @param string $fields,... Additional fields.
   */
  public function autoSortOptions($field) {
    assume(isset($this->model), tr('No model set'));
    $fields = func_get_args();
    foreach ($fields as $field) {
      $this->sortOptions->append(
        new Column($this->model->getLabel($field)),
        $field
      );
    }
  }
}
