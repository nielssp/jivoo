<?php
class BasicDataTableWidget extends TraversableWidget {
  protected $helpers = array('Html', 'Form', 'Pagination', 'Widget', 'Filtering');
  
  protected $options = array(
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
  );
  
  protected function getItems($options) {
    assume($options['model'] instanceof IBasicModel);
    $this->model = $options['model'];
    $this->records = $options['records'];
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
      $records = $this->records;
      usort($records, array($this, 'compareRecords'));
      $this->records = $records;
    }
    
    $this->records = $this->Pagination->paginate($this->records, $options['itemsPerPage']);
    return $this->records;
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
  
  public function handle($item, $options = array()) {
    $primaryKey = $this->model->getFields();
    $primaryKey = $primaryKey[0];
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
          if ($item instanceof ILinkable)
            $cell = $this->Html->link($cell, $item);
        }
        $options['cells'][] = $cell;
      }
    }
    return $this->Widget->widget('DataTableRow', $options);
  }
  
  protected function main($options) {
    $this->setTemplate('widgets/data-table.html');
    return $this->fetch($options);
  }
}

