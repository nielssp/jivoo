<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkObject;
use Jivoo\Routing\ILinkable;

/**
 * Table row filter.
 * @property string $label Filter label.
 * @property string $filter Filter query string.
 */
class Filter extends JtkObject implements ILinkable {
  public function __construct($label, $filter) {
    $this->label = $label;
    $this->filter = $filter;
  }
  
  public function getRoute() {
    return array(
      'query' => array('filter' => $this->filter),
      'mergeQuery' => true
    );
  }
}
