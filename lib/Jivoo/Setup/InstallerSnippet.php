<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Snippets\Snippet;

class InstallerSnippet extends Snippet {
  
  protected $modules = array('Setup');
  
  private $steps = array();
  
  public function getSteps() {
    return $this->steps;
  }
  
  public function addStep($name) {
    assume(is_callable(array($this, $name)));
    $this->steps[$name] = array($this, $name);
  }
  
  public function addInstaller($class, $name = null) {
    if (!isset($name))
      $name = $class;
    $snippet = $this->m->Setup->getInstaller($class);
    $this->steps[$name] = $snippet;
  }

  public function remove($name) {
    if (isset($this->steps[$name]))
      unset($this->steps[$name]);
  }
  
  public function setInit($name) {
    
  }
}