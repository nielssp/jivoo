<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Snippets\Snippet;
use Jivoo\Core\Utilities;
use Jivoo\Models\Form;

abstract class InstallerSnippet extends Snippet {
  
  protected $modules = array('Setup');
  
  protected $helpers = array('Form');
  
  private $steps = array();
  
  private $current = null;
  
  private $parent = null;

  private $installConfig;
  
  protected $dataKey = 'install';
  
  protected $form;
  
  protected function init() {
    $this->form = new Form('install');
    $this->viewData['form'] = $this->form;
    $this->enableLayout();
    $installer = get_class($this);
    $this->installConfig = $this->config['Setup'][$installer];
    $this->setup();
    if (!isset($this->installConfig['current']) or !isset($this->steps[$this->installConfig['current']])) {
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
        $undo = true;
      else
        $undo = null;
    }
    $step = new InstallerStep();
    $step->do = array($this, $name);
    $step->undo = $undo;
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
    $step = new InstallerStep();
    $step->installer = $snippet;
    $step->name = $name;
    $last = $this->getLast();
    if (isset($last)) {
      $step->previous = $this->getLast();
      $last->next = $step;
    }
    $last = $snippet->getLast();
    if (isset($last) and $last->isUndoable())
      $step->undo = true;
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
      $this->installConfig['done'] = true;
      if (isset($this->parent)) {
        return $this->parent->next();
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
  
  private function setCurrent($step) {
    if ($step instanceof InstallerStep)
      $this->current = $step;
    else
      $this->current = $this->steps[$step];
    $this->installConfig['current'] = $this->current->name;
  }
  
  public function jump($step) {
    $this->setCurrent($step);
    return $this->refresh();
  }
  
  public function get() {
    $current = $this->current;
    if (isset($current->installer))
      return $current->installer->__invoke();
    return call_user_func($current->do);
  }
  
  private function undoStep(InstallerStep $step) {
    $this->setCurrent($step);
    if (isset($step->installer)) {
      $last = $step->installer->getLast();
      $step->installer->installConfig['done'] = false;
      return $step->installer->undoStep($last);
    }
    if ($step->undo === true)
      return $this->refresh();
    return call_user_func($step->undo);
  }
  
  private function tryBack() {
    $current = $this->current;
    if (isset($current->previous)) {
      if ($current->previous->isUndoable()) {
        return $this->undoStep($current->previous);
      }
    }
    else if (isset($this->parent)) {
      return $this->parent->tryBack();
    }
    return call_user_func($current->do);
  }
  
  public function post($data) {
    $current = $this->current;
    if (isset($current->installer))
      return $current->installer->__invoke();
    if (isset($this->request->data['back'])) {
      return $this->tryBack();
    }
    return call_user_func($current->do, $data);
  }
  
  public function saveConfig() {
    if ($this->config->save())
      return $this->refresh();
    return $this->render('setup/save-config.html');
  }
  
  public function saveConfigAndContinue() {
    if ($this->config->save())
      return $this->next();
    return $this->render('setup/save-config.html');
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
    $this->Form->formFor($this->form, null);
    return parent::render($templateName);
  }
}


class InstallerStep {
  public $name = null;
  public $next = null;
  public $previous = null;

  public $installer = null;
  public $do = null;
  public $undo = null;
  
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