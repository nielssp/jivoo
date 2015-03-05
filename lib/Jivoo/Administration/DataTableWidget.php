<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration;

/**
 * An advanced data table snippet. Use {@see DataTableSettings} as a parameter
 * to configure.
 */
class DataTable extends Snippet {
  protected $helpers = array('Html', 'Form', 'Pagination', 'Widget', 'Filtering');
  
  /**
   * {@inheritdoc}
   */
  protected $parameters = array('settings');
  
  protected $options = array(
    'model' => null,
    'selection' => null,
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
  );
  
  protected function getItems($options) {
    assume($options['model'] instanceof IModel);
    $this->model = $options['model'];
     if (isset($options['selection']))
      $this->records = $options['selection'];
    else
      $this->records = $options['model'];
    if (isset($options['columns']))
      $this->columns = $options['columns'];
    else
      $this->columns = $this->model->getFields();
    foreach ($this->columns as $column) {
      if (!isset($options['labels'][$column]))
        $options['labels'][$column] = $this->model->getLabel($column);
    }
    $this->actions = $options['actions'];
    $this->primaryColumn = $options['primaryColumn'];
    
    $this->sortBy = $options['defaultSortBy'];
    $this->sortOptions = $options['sortOptions'];
    if (!isset($this->sortOptions)) {
      $this->sortOptions = $options['columns'];
    }
    else {
      foreach ($this->sortOptions as $column) {
        if (!isset($options['labels'][$column]))
          $options['labels'][$column] = $this->model->getLabel($column);
      }
    }
    $this->labels = $options['labels'];
    if (!isset($this->primaryColumn))
      $this->primaryColumn = $this->columns[0];
    
    $this->records = $this->Filtering->apply($this->records);
    
    if (!isset($this->sortBy))
      $this->sortBy = $this->sortOptions[0];
    $this->primaryAction = $options['primaryAction'];
    if (isset($this->request->query['sortBy'])) {
      if ($this->model->hasField($this->request->query['sortBy'])) {
        $this->sortBy = $this->request->query['sortBy'];
      }
    }
    $this->descending = $options['defaultDescending'];
    if (isset($this->request->query['order']))
      $this->descending = ($this->request->query['order'] == 'desc');
    if (isset($this->sortBy)) {
      if ($this->descending)
        $this->records = $this->records->orderByDescending($this->sortBy);
      else
        $this->records = $this->records->orderBy($this->sortBy);
    }
    
    $this->records = $this->Pagination->paginate($this->records, $options['itemsPerPage']);
    return $this->records;
  }
  
  public function handle($item, $options = array()) {
    $primaryKey = $this->model->getAiPrimaryKey(); // TODO
    $options = array_merge(array(
      'record' => $item,
      'id' => $item->$primaryKey,
      'model' => $this->model,
      'columns' => $this->columns,
      'labels' => $this->labels,
      'primaryColumn' => $this->primaryColumn,
      'actions' => $this->actions
    ), $options);
    if (!isset($options['cells'])) {
      $options['cells'] = array();
      foreach ($this->columns as $column) {
        $type = $this->model->getType($column)->type;
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
        if ($column == $this->primaryColumn) {
          if (isset($this->primaryAction))
            $cell = $this->Html->link($cell, $item->action($this->primaryAction));
          else if ($item instanceof ILinkable)
            $cell = $this->Html->link($cell, $item);
        }
        $options['cells'][] = $cell;
      }
    }
    return $this->Widget->widget('DataTableRow', $options);
  }
  
  protected function main($options) {
    return $this->fetch($options);
  }
}

