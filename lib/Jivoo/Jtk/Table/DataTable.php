<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkObject;
use Jivoo\Jtk\JtkCollection;
use Jivoo\Models\IBasicRecord;
use Jivoo\Core\ObjectMacro;

/**
 * A data table.
 * @property Jivoo\Models\IBasicModel $model Model.
 * @property ObjectMacro|IBasicSelection|IBasicRecord[] $selection Content of table.
 * If $model is a {@see Jivoo\Models\IModel} then a {@see IBasicSelection} is
 * expected. The default is an {@see ObjectMacro} that records method calls that
 * will later be applied to the model when creating a selection.
 * @property string $primaryKey Name of primary key field.
 * @property JtkCollection $columns Collection of {@see Column}s.
 * @property JtkCollection $sortOptions Collection of {@see Column}s.
 * @property JtkCollection $filters Collection of {@see Filter}s.
 * @property JtkCollection $actions Collection of row {@see Action}s.
 * @property JtkCollection $bulkActions Collection of bulk {@see Action}s.
 * @property int $rowsPerPage Number of rows to display per page.
 * @property string $id Optional HTML id for table.
 * @property Column $sortBy A reference to the column used for sorting.
 * @property callable $rowHandler A function to call on each row before
 * rendering.
 */
class DataTable extends JtkObject {
  /**
   * Construct data table.
   */
  public function __construct() {
    $this->records = array();
    $this->selection = new ObjectMacro();
    $this->columns = new JtkCollection('Jivoo\Jtk\Table\Column');
    $this->sortOptions = new JtkCollection('Jivoo\Jtk\Table\Column');
    $this->filters = new JtkCollection('Jivoo\Jtk\Table\Filter');
    $this->actions = new JtkCollection('Jivoo\Jtk\Table\Action');
    $this->bulkActions = new JtkCollection('Jivoo\Jtk\Table\Action');
    $this->rowsPerPage = 10;
    $this->id = '';
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
        new Column($this->model->getLabel($field), $field),
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
        new Column($this->model->getLabel($field), $field),
        $field
      );
    }
  }
  
  /**
   * Set function to be called before rendereing each row.
   * @param callable $callable Function that accepts one parameter of type
   * {@see Row}.
   */
  public function eachRow($callable) {
    $this->rowHandler = $callable;
  }
  
  /**
   * Create a row based on a record.
   * @param IBasicRecord $record Record.
   * @return Row Resulting row.
   */
  public function createRow(IBasicRecord $record) {
    $row = new Row($this);
    $row->record = $record;
    if (isset($this->primaryKey)) {
      $field = $this->primaryKey;
      $row->id = $record->$field;
    }
    foreach ($row->cells as $cell)
      $cell->value = $cell->column->render($this, $record);
    if (isset($this->rowHandler))
      call_user_func($this->rowHandler, $row);
    return $row;
  }
}
