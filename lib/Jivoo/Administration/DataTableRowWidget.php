<?php
class DataTableRowWidget extends Widget {
  protected $options = array(
    'id' => '',
    'record' => null,
    'cells' => array(),
    'columns' => array(),
    'labels' => array(),
    'primaryColumn' => null,
    'actions' => array(),
    'removeActions' => null
  );
  
  public function main($options) {
    if (count($options['actions']) > 0)
      assume($options['record'] instanceof IActionRecord);
    if (isset($options['removeActions'])) {
      $options['actions'] = array_diff_key(
        $options['actions'],
        array_flip($options['removeActions'])
      );
    }
    return $this->fetch($options);
  }
}

