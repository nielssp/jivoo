<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Helpers\Helper;

/**
 * Jivoo toolkit helper.
 */
class JtkHelper extends Helper {
  
  protected $modules = array('Jtk', 'Themes');
  
  protected $helpers = array('Snippet');
  
  public function __get($tool) {
    $tool = $this->m->Jtk->getTool($tool);
    return $tool;
  }
  
  public function __call($tool, $parameters) {
    $tool = $this->m->Jtk->getTool($tool);
    if (isset($tool)) {
      return $tool->__invoke($parameters);
    }
    throw new \InvalidMethodException(tr('Invalid method: %1', $tool));
  }
  
  public function setTheme($theme) {
    $this->m->Themes->load($theme);
  }
}