<?php
class RecordIndexWidget extends Widget {
  protected $helpers = array('Form', 'Pagination');
  
  protected $options = array(
    'model' => null,
    'selection' => null,
    'id' => '',
    'filters' => array(),
    'columns' => array(),
    'sortBy' => null,
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
    $this->Pagination->setCount($this->records->count());
    $this->Pagination->setLimit($options['itemsPerPage']);
    $this->records = $this->Pagination->paginate($this->records);
    return $this->fetch($options);
  }
}

