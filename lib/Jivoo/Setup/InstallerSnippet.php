<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Snippets\Snippet;
use Jivoo\Core\Utilities;

abstract class InstallerSnippet extends Snippet {
  
  protected $modules = array('Setup');
  
  private $steps = array();
  
  private $connections = array();

  private $installConfig;
  
  private $action = null;
  
  private $first = null;
  
  private $last = null;
  
  protected function init() {
    $installer = get_class($this);
    $this->installConfig = $this->config['Setup'][$installer];
    $this->setup();
  }
  
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

  abstract protected function setup();
  
  public function done() {
    return $this->refresh();
  }

  public function undone() {
    return $this->refresh();
  }
  
  public function next($step1, $step2) {
    if (!isset($step1)) {
      $this->first = $step2;
      return;
    }
    if (!isset($step2)) {
      $this->last = $step1;
      return;
    }
    if (!isset($this->connections[$step1]))
      $this->connections[$step1] = array();
    $this->connections[$step1]['next'] = $step2;
  }
  
  public function back($step1, $step2) {
    if (!isset($step1) or !isset($step2))
      return;
    if (!isset($this->connections[$step1]))
      $this->connections[$step1] = array();
    $this->connections[$step1]['back'] = $step2;
    
  }
  
  public function connect($step1, $step2) {
    $this->next($step1, $step2);
    $this->back($step2, $step1);
  }
  
  public function handle($action) {
    if ($action == 'do')
      $this->done();
    else if ($action == 'undo')
      $this->undone();
  }
  
  public function get($action = null, $current = null) {
    if (!isset($current)) {
      $current = $this->first;
      if (isset($this->installConfig['current'])) {
        $current = $this->installConfig['current'];
      }
    }
    $this->enableLayout();
    return call_user_func($this->steps[$current], $action);
  }
  
  public function post() {
    if (isset($this->request->data['next']))
      return $this->get('do');
    else if (isset($this->request->data['back'])) {
      if (isset($this->installConfig['current'])) {
        $current = $this->installConfig['current'];
        if (isset($this->connections[$current]) and
            isset($this->connections[$current]['back'])) {
          return $this->get('undo', $this->connections[$current]['back']);
        }
      }
    }
    return $this->get();
  }
  
  /**
   * {@inheritdoc}
   */
  protected function render($templateName = null) {
    if (!isset($templateName)) {
      list(, $caller) = debug_backtrace(false);
      $class = str_replace($this->app->n('Snippets\\'), '', $caller['class']);
      $dirs = array_map(array('Jivoo\Core\Utilities', 'camelCaseToDashes'), explode('\\', $class));
      $templateName = implode('/', $dirs) . '/';
      $templateName .= Utilities::camelCaseToDashes($caller['function']) . '.html';
    }
    return parent::render($templateName);
  }
}