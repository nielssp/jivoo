<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Loads and keeps track of Jivoo modules.
 * @property-read \Jivoo\AccessControl\AccessControl $AccessControl
 * @property-read \Jivoo\ActiveModels\ActiveModels $ActiveModels
 * @property-read \Jivoo\Assets\Assets $Assets
 * @property-read \Jivoo\Console\Console $Console
 * @property-read \Jivoo\Content\Content $Content
 * @property-read \Jivoo\Controllers\Controllers $Controllers
 * @property-read \Jivoo\Databases\Databases $Databases
 * @property-read \Jivoo\Extensions\Extensions $Extensions
 * @property-read \Jivoo\Helpers\Helpers $Helpers
 * @property-read \Jivoo\Jtk\Jtk $Jtk
 * @property-read \Jivoo\Migrations\Migrations $Migrations
 * @property-read \Jivoo\Models\Models $Models
 * @property-read \Jivoo\Routing\Routing $Routing
 * @property-read \Jivoo\Setup\Setup $Setup
 * @property-read \Jivoo\Snippets\Snippets $Snippets
 * @property-read \Jivoo\Themes\Themes $Themes
 * @property-read \Jivoo\View\View $View
 */
class ModuleLoader implements \ArrayAccess {


  public function load($module) {
    if (!isset($this->m->$module)) {
      $this->triggerEvent('beforeLoadModule', new LoadModuleEvent($this, $module));
      if (!isset($this->imports[$module]))
        $this->import($module);
      $class = $this->imports[$module];
      if (isset($this->optionalDependencies[$module])) {
        foreach ($this->optionalDependencies[$module] as $dependency) {
          if (isset($this->imports[$dependency]))
            $this->load($dependency);
        }
      }
      Lib::assumeSubclassOf($class, 'Jivoo\Core\LoadableModule');
      $this->m->$module = new $class($this);
      $this->triggerEvent('afterLoadModule', new LoadModuleEvent($this, $module, $this->m->$module));
      $this->m->$module->afterLoad();
      if (isset($this->waitingCalls[$module])) {
        foreach ($this->waitingCalls[$module] as $tuple) {
          list($method, $args) = $tuple;
          call_user_func_array(array($this->m->$module, $method), $args);
        }
      }
    }
    return $this->m->$module;
  }
}