<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration;

use Jivoo\Helpers\Helper;

class AdminHelper extends Helper {
  
  protected $modules = array('Assets', 'View', 'Administration');
  
  protected $helpers = array('Html', 'Snippet');
  
  public function menu($menu = 'main') {
    return $this->Snippet->__call('Jivoo\Administration\Menu\IconMenuWidget', array(
      'menu' => $this->m->Administration->menu[$menu],
    ));
  }
  
}