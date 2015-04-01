<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration;

class TableAction {
  public $label;
  public $route;
  public $icon;
  public $method;
  public $data;
  public $confirmation;

  public function __construct($label, $route, $icon = null, $data = array(), $method = 'post', $confirmation = null) {
    $this->label = $label;
    $this->route = $route;
    $this->icon = $icon;
    $this->method = $method;
    $this->data = $data;
    $this->confirmation = $confirmation;
  }
}