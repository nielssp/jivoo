<?php
class DataTableHelper extends Helper {
  protected $helpers = array('Html', 'Filtering', 'Pagination', 'Widget');
  
  private $defaultOptions = array(
    'model' => null,
    'selection' => null,
    'id' => '',
    'filters' => array(),
    'columns' => array(),
    'sortBy' => null,
    'defaultSortBy' => null,
    'defaultDescending' => false, 
    'itemsPerPage' => 20,
    'defaultAction' => null,
    'bulkActions' => array(),
    'recordActions' => array(),
  );
  
  private $options = array();
  private $model = null;
  private $data = array();
  
  public function __get($name) {
    switch ($name) {
      case 'data':
        return $this->$name;
    }
    return parent::__get($name);
  }

  public function begin($options = array()) {
    $options = array_merge($this->defaultOptions, $options);
    assume($options['model'] instanceof IModel);
    $this->model = $options['model'];
    $this->data = isset($options['selection'])
      ? $options['selection']
      : $options['model'];
    if (!isset($options['sortBy']))
      $options['sortBy'] = $options['columns'];
    $this->sortBy = $options['defaultSortBy'];
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
        $this->data = $this->data->orderByDescending($this->sortBy);
      else
        $this->data = $this->data->orderBy($this->sortBy);
    }
    
    $this->data = $this->Pagination->paginate($this->data, $options['itemsPerPage']);
    $this->options = $options;
    $this->view->begin('table-body');
  }
  
  public function end() {
    $this->view->end();
    return $this->Widget->widget('DataTable', $this->options);
  }
  
  public function row($options = array()) {
    $options = array_merge(array(
      ''
    ), $options);
    return $this->Widget->widget('DataTableRow', $options);
  }
  
  public function table($options = array()) {
    $html = $this->begin($options);
    foreach ($this->data as $row) {
      $html .= $this->row(array(
        
      ));
    }
    $html = $this->end();
    return $html;
  }
}