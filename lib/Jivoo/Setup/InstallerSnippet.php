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
  
  private $current = null;

  private $installConfig;
  
  private $parent = null;
  
  protected function init() {
    $this->enableLayout();
    $installer = get_class($this);
    $this->installConfig = $this->config['Setup'][$installer];
    $this->setup();
    if (!isset($this->installConfig['current'])) {
      $head = array_keys(array_slice($this->steps, 0, 1));
      $this->installConfig['current'] = $head[0];
    }
    $this->current = $this->steps[$this->installConfig['current']];

    if (isset($this->current->previous)) {
      $this->view->data->enableBack = $this->current->previous->isUndoable();
    }
    $this->view->data->enableNext = true;
  }

  abstract protected function setup();
  
  public function getSteps() {
    return $this->steps;
  }
  
  public function appendStep($name, $undoable = false) {
    assume(is_callable(array($this, $name)));
    $undo = array($this, 'undo' . ucfirst($name));
    if (!method_exists ($this, $undo[1])) {
      if ($undoable)
        $undo = array($this, 'back');
      else
        $undo = null;
    }
    $step = new InstallerStep(array($this, $name), $undo);
    $step->name = $name;
    $last = $this->getLast();
    if (isset($last)) {
      $step->previous = $this->getLast();
      $last->next = $step;
    }
    $this->steps[$name] = $step;
  }
    
  public function appendInstaller($class, $name = null) {
    if (!isset($name))
      $name = $class;
    $snippet = $this->m->Setup->getInstaller($class);
    $snippet->parent = $this;
    $step = new SubInstallerStep($snippet);
    $step->name = $name;
    $last = $this->getLast();
    if (isset($last)) {
      $step->previous = $this->getLast();
      $last->next = $step;
    }
    $this->steps[$name] = $step;
  }
  
  public function getFirst() {
    $slice = array_values(array_slice($this->steps, 0, 1));
    return $slice[0];
  }

  public function getLast() {
    $slice = array_values(array_slice($this->steps, -1, 1));
    return $slice[0];
  }

  public function remove($name) {
    if (isset($this->steps[$name]))
      unset($this->steps[$name]);
  }
  
  public function next() {
    if (!isset($this->current->next)) {
      if (isset($this->parent)) {
        return $this->parent->next();
      }
      else {
        $this->installConfig['done'] = true;
      }
    }
    else {
      $this->installConfig['current'] = $this->current->next->name;
    }
    return $this->refresh();
  }
  
  public function back() {
    if (!isset($this->current->previous)) {
      if (isset($this->parent)) {
        return $this->parent->back();
      }
    }
    else {
      $this->installConfig['current'] = $this->current->previous->name;
    }
    return $this->refresh();
  }
  
  public function jump($step) {
    return $this->refresh();
  }
  
  public function get() {
    return $this->current->__invoke(null);
  }
  
  public function post() {
    if (isset($this->request->data['next'])) {
      return $this->current->__invoke($this->request->data);
    }
    else if (isset($this->request->data['back'])) {
      return $this->current->__invoke($this->request->data);
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


class InstallerStep {
  public $name = null;
  public $next = null;
  public $previous = null;
  
  private $do;
  private $undo;
  
  public function __construct($do, $undo = null) {
    $this->do = $do;
    $this->undo = $undo;
  }

  public function __invoke($data) {
    if (isset($data['next'])) {
      return call_user_func($this->do, $data);
    }
    else if (isset($data['back'])) {
      if (isset($this->previous) and $this->previous->isUndoable()) {
        return $this->previous->undo();
      }
    }
    return call_user_func($this->do, null);
  }

  public function undo() {
    return call_user_func($this->undo);
  }
  
  public function isUndoable() {
    return isset($this->undo);
  }
  
  public function isLast() {
    return !isset($this->next);
  }

  public function isFirst() {
    return !isset($this->previous);
  }
}

class SubInstallerStep {
  private $installer;
  private $undoable = false;
  public function __construct(InstallerSnippet $installer) {
    $this->installer = $installer;
    $last = $installer->getLast();
    if (isset($last))
      $this->undoable = $last->isUndoable();
  }
  
  public function __invoke($data) {
    return $this->installer->__invoke();
  }
  
  public function undo() {
    if ($this->undoable)
      return $this->installer->getLast()->undo();
  }
  
  public function isUndoable() {
    return $this->undoable;
  }
}