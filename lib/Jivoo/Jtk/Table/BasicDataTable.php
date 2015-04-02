<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkSnippet;
use Jivoo\Routing\ILinkable;
use Jivoo\Models\DataType;

class BasicDataTable extends JtkSnippet {
  protected $helpers = array('Jtk', 'Form', 'Pagination', 'Filtering');
  
  protected $viewData = array(
    'model' => null,
    'records' => array(),
    'id' => '',
    'filters' => array(),
    'columns' => null,
    'labels' => array(),
    'addRoute' => null,
    'primaryColumn' => null,
    'sortOptions' => null,
    'defaultSortBy' => null,
    'defaultDescending' => false, 
    'itemsPerPage' => 10,
    'primaryAction' => null,
    'bulkActions' => array(),
    'actions' => array(),
    'rowHandler' => null,
  );
  
  protected $autoSetters = array('model', 'records', 'id', 'itemsPerPage', 'primaryColumn');
  
  protected function init() {
    $this->viewData['rowHandler'] = array($this, 'handle');
  }
  
  public function eachRow($function) {
    $this->viewData['rowHandler'] = $function;
    return $this;
  }

  public function compareRecords(IBasicRecord $a, IBasicRecord $b) {
    $field = $this->sortBy;
    if ($a->$field == $b->$field)
      return 0;
    if ($this->descending) {
      if (is_numeric($a->$field))
        return $b->$field - $a->$field;
      return strcmp($b->$field, $a->$field);
    }
    else {
      if (is_numeric($a->$field))
        return $a->$field - $b->$field;
      return strcmp($a->$field, $b->$field);
    }
  }
  
  public function handle($item) {
    $row = $this->Jtk->DataTableRow;
    $primaryKey = $this->viewData['model']->getFields();
    $primaryKey = $primaryKey[0];
    $row->id($item->$primaryKey);
    $row->columns($this->viewData['columns']);
    $row->labels($this->viewData['labels']);
    $row->primaryColumn($this->viewData['primaryColumn']);
    $row->actions($this->viewData['actions']);
    $cells = array();
    foreach ($this->viewData['columns'] as $column) {
      $type = $this->viewData['model']->getType($column)->type;
      $cell = null;
      switch ($type) {
        case DataType::DATE:
          $cell = fdate($item->$column);
          break;
        case DataType::DATETIME:
          $cell = ldate($item->$column);
          break;
        case DataType::BOOLEAN:
          $cell = $item->$column ? tr('Yes') : tr('No');
          break;
        default:
          $cell = h($item->$column);
          break;
      }
      if ($column == $this->viewData['primaryColumn']) {
        if ($item instanceof ILinkable)
          $cell = $this->Html->link($cell, $item);
      }
      $cells[] = $cell;
    }
    $row->cells($cells);
    return $row();
  }
  
  public function get() {
    $model = $this->viewData['model'];
    $records = $this->viewData['records'];
    if (isset($options['columns']))
      $columns = $options['columns'];
    else
      $columns = $model->getFields();
    foreach ($columns as $column) {
      if (!isset($this->viewData['labels'][$column]))
        $this->viewData['labels'][$column] = $model->getLabel($column);
    }
    
    if (!isset($this->viewData['sortOptions'])) {
      $this->viewData['sortOptions'] = $this->viewData['columns'];
    }
    else {
      foreach ($sortOptions as $column) {
        if (!isset($this->viewData['labels'][$column]))
          $this->viewData['labels'][$column] = $model->getLabel($column);
      }
    }
    if (!isset($this->viewData['primaryColumn']))
      $this->viewData['primaryColumn'] = $columns[0];
    
    
    $records = $this->Filtering->apply($records);

    $sortBy = $this->viewData['defaultSortBy'];
    if (!isset($sortBy))
      $sortBy = $sortOptions[0];
    if (isset($this->request->query['sortBy'])) {
      if ($model->hasField($this->request->query['sortBy'])) {
        $sortBy = $this->request->query['sortBy'];
      }
    }
    $this->viewData['sortBy'] = $sortBy;
    $descending = $this->viewData['defaultDescending'];
    if (isset($this->request->query['order']))
      $descending = ($this->request->query['order'] == 'desc');
    $this->viewData['descending'] = $descending;
    if (isset($sortBy)) {
      $records = $records;
      usort($records, array($this, 'compareRecords'));
      $records = $records;
    }
    
    $this->viewData['records'] = $this->Pagination->paginate($records, $this->viewData['itemsPerPage']);
    $this->viewData['columns'] = $columns;
    return $this->render('jivoo/jtk/table/data-table.html');
  }
}

