<?php
class DataTableWidget extends Widget {
  protected $helpers = array('Form', 'Pagination');
  
  protected $options = array(
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
    'recordTemplate' => 'widgets/record.html',
  );
  
  public function main($options) {
    assume($options['model'] instanceof IModel);
    $this->model = $options['model'];
    $this->records = isset($options['selection'])
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
        $this->records = $this->records->orderByDescending($this->sortBy);
      else
        $this->records = $this->records->orderBy($this->sortBy);
    }
    
    $this->records = $this->Pagination->paginate($this->records, $options['itemsPerPage']);
    return $this->fetch($options);
  }
}

