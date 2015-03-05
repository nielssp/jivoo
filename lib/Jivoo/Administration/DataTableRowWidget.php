<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration;

use Jivoo\Snippets\Snippet;

/**
 * A row of a {@see DataTable} or {@see BasicDataTable}. 
 */
class DataTableRow extends Snippet {
  /**
   * {@inheritdoc}
   */
  protected $parameters = array('settings', 'item');
  
  public function get() {
    return $this->render();
  }
  
  protected $options = array(
    'id' => '',
    'record' => null,
    'cells' => array(),
    'columns' => array(),
    'labels' => array(),
    'primaryColumn' => null,
    'actions' => array(),
    'removeActions' => null,
    'class' => null
  );
  
  public function main($options) {
    if (isset($options['removeActions'])) {
      $options['actions'] = array_diff_key(
        $options['actions'],
        array_flip($options['removeActions'])
      );
    }
    return $this->fetch($options);
  }
}

