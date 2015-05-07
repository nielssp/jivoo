<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkSnippet;

/**
 * A row of a {@see DataTable} or {@see BasicDataTable}. 
 */
class DataTableRow extends JtkSnippet {
  protected $viewData = array(
    'id' => null,
    'cells' => array(),
    'columns' => array(),
    'labels' => array(),
    'primaryColumn' => null,
    'actions' => array(),
    'removeActions' => null,
    'class' => null
  );
  
  protected $autoSetters = array(
    'id', 'cells', 'columns', 'labels', 'primaryColumn', 'actions', 'class'
  );
  
  public function cellAt($offset, $value) {
    $this->viewData['cells'][$offset] = $value;
    return $this;
  }
  
  public function get() {
    return $this->render();
  }
}

