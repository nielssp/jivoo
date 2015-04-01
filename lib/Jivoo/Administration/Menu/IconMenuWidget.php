<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration\Menu;

use Jivoo\Snippets\Snippet;

class IconMenuWidget extends Snippet {
  
  protected $helpers = array('Icon');
  
  protected $options = array(
    'menu' => array(),
    'defaultAction' => '*',
    'defaultParameters' => '*'
  );
  
  private $menu;
  
  public function get() {
    $options = $this->options;
    $this->menu = $options['menu'];
    if (!isset($this->menu))
      $this->menu = array();
    return $this->render('widgets/icon-menu.html');
  }
}