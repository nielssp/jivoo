<?php
class RecordIndexWidget extends Widget {
  protected $helpers = array('Form', 'Pagination');
  
  protected $options = array(
    'model' => null,
    'selection' => null,
    'id' => '',
    'filters' => array(),
    'columns' => array(),
    'primary' => null,
    'sortBy' => null,
    'defaultAction' => null,
    'bulkActions' => array(),
    'record' => array(
      'template' => null,
      'actions' => array()
    ),
  );
  
  public function main($options) {
    assume($options['model'] instanceof IModel);
    $this->model = $options['model'];
    $this->records = isset($options['selection'])
      ? $options['selection']
      : $options['model'];
    if (!isset($options['sortBy']))
      $options['sortBy'] = $options['columns'];
    if (!isset($options['primary']))
      $options['primary'] = $options['columns'][0];
    $this->Pagination->setCount($this->records->count());
    $this->Pagination->setLimit(2);
    $this->records = $this->Pagination->paginate($this->records);
    return $this->fetch($options);
  }
}
