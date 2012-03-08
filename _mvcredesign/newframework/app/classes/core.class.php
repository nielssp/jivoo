<?php

class Core {
  private $modules = array();
  // Inject modules into core
  public function loadModule($module) {
    if (!file_exists(PATH . APP . MODULES . $module . '.class.php')) {
      throw new ModuleNotFoundException(tr('The "%1" module could not be found', $module));
    }
    require_once(PATH . APP . MODULES . $module . '.class.php');
    $className = ucfirst($module);
//     $this->modules[] = $className::load($this); // PHP 5.3 only :-(
    $this->modules[] = call_user_func(array($className, 'load'), $this);
  }
}

class ModuleNotFoundException extends Exception { }