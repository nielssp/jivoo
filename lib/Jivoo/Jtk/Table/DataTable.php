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
 * @property IModel $model Model.
 * @property JtkCollection $columns Collection of {@see Column}s.
 * @property JtkCollection $filters Collection of {@see Filter}s.
 * @property JtkCollection $actions Collection of row {@see Action}s.
 * @property JtkCollection $bulkActions Collection of bulk {@see Action}s.
 */
class DataTable extends JtkObject {
  
  public function __construct() {
    $this->columns = new JtkCollection('Jivoo\Jtk\Table\Column');
    $this->filters = new JtkCollection('Jivoo\Jtk\Table\Filter');
    $this->actions = new JtkCollection('Jivoo\Jtk\Table\Action');
    $this->bulkActions = new JtkCollection('Jivoo\Jtk\Table\Action');
  }
  
  /**
   * Create a column and append it.
   * @param string $label Column label.
   * @param string $id Optional id.
   * @return Column Column.
   */
  public function appendColumn($label, $id = null) {
    $column = new Column($label);
    $this->columns->append($column, $id);
    return $column;
  }

  /**
   * Create a column and prepend it.
   * @param string $label Column label.
   * @param string $id Optional id.
   * @return Column Column.
   */
  public function prependColumn($label, $id = null) {
    $column = new Column($label);
    $this->columns->prepend($column, $id);
    return $column;
  }

  /**
   * Create a column and insert it.
   * @param int $offset Offset.
   * @param string $label Column label.
   * @param string $id Optional id.
   * @return Column Column.
   */
  public function insertColumn($offset, $label, $id = null) {
    $column = new Column($label);
    $this->columns->insert($offset, $column, $id);
    return $column;
  }
  
  /**
   * Create a filter and append it.
   * @param string $label Filter label.
   * @param string $filter Filter query string.
   * @param string $id Optional id.
   * @return Column Column.
   */
  public function appendFilter($label, $filter, $id = null) {
    $filter = new Filter($label, $filter);
    $this->columns->append($filter, $id);
    return $filter;
  }

  /**
   * Create a column and prepend it.
   * @param string $label Filter label.
   * @param string $filter Filter query string.
   * @param string $id Optional id.
   * @return Filter Filter.
   */
  public function prependFilter($label, $filter, $id = null) {
    $filter = new Filter($label, $filter);
    $this->columns->prepend($filter, $id);
    return $filter;
  }

  /**
   * Create a filter and insert it.
   * @param int $offset Offset.
   * @param string $label Filter label.
   * @param string $filter Filter query string.
   * @param string $id Optional id.
   * @return Filter Filter.
   */
  public function insertFilter($offset, $label, $filter, $id = null) {
    $filter = new Filter($label, $filter);
    $this->columns->insert($offset, $filter, $id);
    return $filter;
  }
}
