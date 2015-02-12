<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Event;

/**
 * Active record/model system.
 * @package Jivoo\ActiveModels
 */
class ActiveModels extends LoadableModule {

  protected static $loadAfter = array('Migrations');

  /**
   * {@inheritdoc}
   */
  protected $modules = array('Models', 'Databases');
  
  /**
   * @var ActiveModel[] Mapping of model names to models.
   */
  private $models = array();
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
    $classes = $this->m->Models->getModelClasses();
    foreach ($classes as $class) {
      $this->addActiveModel($class);
    }
    $this->m->Routing->attachEventHandler('beforeRender', array($this, 'installModels'));
  }

  /**
   * Installs new models.
   * @param Event Empty event.
   */
  public function installModels(Event $event) {
    foreach ($this->models as $name => $model) {
      if ($model instanceof ActiveModel
        and !(isset($this->config['installed'][$name])
          and $this->config['installed'][$name])) {
        $model->triggerEvent('install', new ActiveModelEvent($this));
        $this->config['installed'][$name] = true;
      }
    }
  } 
  
  /**
   * Add an active model.
   * @param string $class Class name of active model.
   * @return True if successfull, false if table not found.
   */
  public function addActiveModel($class) {
    if (is_subclass_of($class, 'ActiveModel')) {
      $model = new $class($this->app, $this->m->Databases);
      $this->m->Models->setModel($class, $model);
      $this->models[$class] = $model;
      return true;
    }
    return false;
  }

  /**
   * Add an active model if it has not already been added.
   * @param string $class Class name of active model.
   * @param string $file Path to model class file.
   * @return True if missing and added successfully, false otherwise.
   */
  public function addActiveModelIfMissing($name, $file) {
    if (isset($this->m->Models->$name)) {
      return false;
    }
    if (!Lib::classExists($name, false)) {
      include $file;
    }
    return $this->addActiveModel($name);
  }
}
