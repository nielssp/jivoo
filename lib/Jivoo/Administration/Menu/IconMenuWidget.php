<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration\Menu;

class IconMenuWidget extends Widget {
  
  protected $helpers = array('Widget', 'Icon');
  
  protected $options = array(
    'menu' => array(),
    'defaultAction' => '*',
    'defaultParameters' => '*'
  );
  
  public function main($options) {
    $this->menu = $options['menu'];
    if (!isset($this->menu))
      $this->menu = array();
    return $this->fetch();
  }
}