<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Snippets\Snippet;
use Jivoo\Core\Utilities;
use Jivoo\Models\Form;
use Jivoo\Routing\ResponseOverrideException;
use Jivoo\Routing\TextResponse;
use Jivoo\Core\Store\Config;
use Jivoo\Core\Logger;

/**
 * An installer. Consists of a number of steps (implemented as methods) and 
 * subinstallers.
 */
abstract class InstallerSnippet extends Snippet {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Setup');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form');
  
  /**
   * @var InstallerStep[] Steps.
   */
  private $steps = array();
  
  /**
   * @var InstallerStep Current step.
   */
  private $current = null;
  
  /**
   * @var InstallerSnippe Parent installer.
   */
  private $parent = null;

  /**
   * @var Config Installer state.
   */
  private $installConfig;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->enableLayout();
    $installer = get_class($this);
  }
  
  /**
   * Set installer state.
   * @param Config $config State.
   */
  public function setConfig(Config $config) {
    $this->installConfig = $config;
    $this->setup();
    if (!isset($this->installConfig['current']) or !isset($this->steps[$this->installConfig['current']])) {
      $head = array_keys(array_slice($this->steps, 0, 1));
      $this->installConfig['current'] = $head[0];
    }
    $this->current = $this->steps[$this->installConfig['current']];

    $this->view->data->enableNext = true;
  }

  /**
   * Use this method to set up steps and subinstallers using {@see appendStep()}
   * and {@see appendInstaller()}.
   */
  abstract protected function setup();
  
  /**
   * Get steps.
   * @return InstallerStep[] Installer steps.
   */
  public function getSteps() {
    return $this->steps;
  }
  
  /**
   * Append a step. If the name of the step is 'step' and a method with the
   * name 'undoStep' exists, $undoable is set to true.
   * @param string $name Name of step and method.
   * @param string $undoable Whether or not step is undoable.
   */
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
  
  /**
   * Append a subinstaller.
   * @param string $class Installer class.
   * @param string $name Optional name for step.
   */
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
  
  /**
   * Get first step.
   * @return InstallerStep|null First step if not empty.
   */
  public function getFirst() {
    $slice = array_values(array_slice($this->steps, 0, 1));
    if (isset($slice[0]))
      return $slice[0];
    return null;
  }

  /**
   * Get last step.
   * @return InstallerStep|null Last step if not empty.
   */
  public function getLast() {
    $slice = array_values(array_slice($this->steps, -1, 1));
    if (isset($slice[0]))
      return $slice[0];
    return null;
  }

  /**
   * Remove a step.
   * @param string $name Step name.
   */
  public function remove($name) {
    if (isset($this->steps[$name]))
      unset($this->steps[$name]);
  }
  
  /**
   * Exit installer.
   */
  public function end() {
    $this->installConfig['done'] = true;
    if (isset($this->parent))
      return $this->parent->next();
    return $this->saveConfig();
  }
  
  /**
   * Go to next step.
   * @return bool False if state could not be updated.
   */
  public function next() {
    if (!isset($this->current->next)) {
      $this->installConfig['done'] = true;
      if (isset($this->parent))
        return $this->parent->next();
      $this->app->config['Setup']['version'] = $this->app->version;
    }
    else {
      $this->installConfig['current'] = $this->current->next->name;
    }
    return $this->saveConfig();
  }

  /**
   * Go to previous step.
   * @return bool False if state could not be updated.
   */
  public function back() {
    if (!isset($this->current->previous)) {
      if (isset($this->parent)) {
        return $this->parent->back();
      }
    }
    else {
      $this->installConfig['current'] = $this->current->previous->name;
    }
    return $this->saveConfig();
  }
  
  /**
   * Set current step.
   * @param string $step Step name.
   */
  private function setCurrent($step) {
    if ($step instanceof InstallerStep)
      $this->current = $step;
    else
      $this->current = $this->steps[$step];
    $this->installConfig['current'] = $this->current->name;
  }
  
  /**
   * Get current step name with class name.
   * @return string Class and step name.
   */
  public function getCurrentStep() {
    if (isset($this->current->installer))
      return $this->current->installer->getCurrentStep();
    return get_class($this) . '::' . $this->current->name;
  }
  
  /**
   * Go to another step.
   * @param string $step Step name.
   */
  public function jump($step) {
    $this->setCurrent($step);
    return $this->saveConfig();
  }
  
  /**
   * If undo is possible.
   * @return bool True if undoable.
   */
  private function isUndoable() {
    if (isset($this->current->previous)) {
      return $this->current->previous->isUndoable();
    }
    else if (isset($this->parent)) {
      return $this->parent->isUndoable();
    }
    return false;
  }
  
  /**
   * {@inheritdoc}
   */
  public function get() {
    $current = $this->current;
    if (isset($current->installer))
      return $current->installer->__invoke();
    Logger::debug(tr('Installer step: %1::%2', get_class($this), $current->name));
    $this->viewData['enableBack'] = $this->isUndoable();
    return call_user_func($current->do, null);
  }
  
  /**
   * Perform undo.
   * @param InstallerStep $step Step.
   * @return mixed
   */
  private function undoStep(InstallerStep $step) {
    $this->setCurrent($step);
    if (isset($step->installer)) {
      $last = $step->installer->getLast();
      $step->installer->installConfig['done'] = false;
      return $step->installer->undoStep($last);
    }
    if ($step->undo === true)
      return $this->saveConfig();
    return call_user_func($step->undo);
  }
  
  /**
   * Try to go back.
   * @return mixed
   */
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
    return call_user_func($current->do, null);
  }

  /**
   * {@inheritdoc}
   */
  public function post($data) {
    $current = $this->current;
    if (isset($current->installer))
      return $current->installer->__invoke();
    if (isset($this->request->data['back'])) {
      return $this->tryBack();
    }
    $this->viewData['enableBack'] = $this->isUndoable();
    return call_user_func($current->do, $data);
  }
  
  /**
   * Run asynchronous task.
   * @param IAsyncTask $task Task object.
   * @return bool True if task is done. 
   */
  public function runAsync(IAsyncTask $task) {
    if ($this->request->hasValidData()) {
      $taskConfig = $this->installConfig->getSubset('async')->getSubset($this->current->name);
      $state = $taskConfig->get('state', array());
      $task->resume($state);
      if ($this->request->isAjax()) {
        $max = 1;
        $start = $_SERVER['REQUEST_TIME'];
        $end = $start + $max;
        header('Content-Type: text/plain');
        header('Cache-Control: no-cache');
        if ($task->isDone()) {
          echo "done:\n";
          exit;
        }
        while (true) {
          try {
            $task->run();
          }
          catch (\Exception $e) {
            Logger::logException($e);
            echo 'error: ' . $e->getMessage() . "\n";
            break;
          }
          $status = $task->getStatus();
          if (isset($status))
            echo 'status: ' . $status . "\n";
          $progress = $task->getProgress();
          if (isset($progress))
            echo 'progress: ' . intval($progress) . "\n";
          if ($task->isDone()) {
            echo "done:\n";
            break;
          }
          if (time() >= $end)
            break;
          ob_flush();
          flush();
        }
        $state = $task->suspend();
        $taskConfig['state'] = $state;
        if (!$taskConfig->save())
          echo 'error: ' . tr('Could not save progress!');
        exit;
      }
      if ($task->isDone())
        return true;
    }
    $this->view->resources->import('setup/async.js');
    return false;
  }
  
  /**
   * Attempt to save configuration, then refresh.
   * @param Config $config Configuration.
   * @return string Response.
   */
  public function saveConfig(Config $config = null) {
    if (!isset($config))
      $config = $this->config;
    if ($config->save())
      return $this->refresh();
    $this->viewData['title'] = tr('Unable to save configuration file');
    $this->viewData['file'] = $config->file;
    $this->viewData['exists'] = file_exists($this->viewData['file']);
    if ($this->viewData['exists']) {
      $perms = fileperms($this->viewData['file']);
      $this->viewData['mode'] = sprintf('%o', $perms & 0777);
    }
    $this->viewData['data'] = '<?php' . PHP_EOL . 'return ' . $config->root->prettyPrint() . ';';
    return $this->render('setup/save-config.html');
  }

  /**
   * Attempt to save configuration, then go to next step.
   * @param Config $config Configuration.
   * @return string Response.
   */
  public function saveConfigAndContinue(Config $config = null) {
    if (!isset($config))
      $config = $this->config;
    if ($config->save())
      return $this->next();
    return $this->saveConfig($config);
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

/**
 * An installer step.
 */
class InstallerStep {
  /**
   * @var string Name of step.
   */
  public $name = null;
  
  /**
   * @var InstallerStep Next step.
   */
  public $next = null;

  /**
   * @var InstallerStep Previous step.
   */
  public $previous = null;

  /**
   * @var InstallerSnippet Installer.
   */
  public $installer = null;
  
  /**
   * @var callable Do function.
   */
  public $do = null;

  /**
   * @var callable Undo function.
   */
  public $undo = null;
  
  /**
   * Whether or not step is undoable.
   * @return bool True if undoable.
   */
  public function isUndoable() {
    return isset($this->undo);
  }
  
  /**
   * Whether or not this is the last step.
   * @return bool True if last.
   */
  public function isLast() {
    return !isset($this->next);
  }

  /**
   * Whether or not this is the first step.
   * @return bool True if first.
   */
  public function isFirst() {
    return !isset($this->previous);
  }
}
