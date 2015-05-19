<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Setup\SetupController;
use Jivoo\Models\Form;
use Jivoo\Core\Lib;
use Jivoo\Databases\DatabaseConnectionFailedException;
use Jivoo\Databases\DatabaseSelectFailedException;

/**
 * Controller for setting up database. 
 */
class DatabaseInstaller extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html', 'Form', 'Jivoo\Databases\DatabaseDrivers');

  public function setup() {
    $this->addStep('select');
    $this->addStep('configure');
    
    $this->setInit('select');
  }
  
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->view->addTemplateDir($this->p('Jivoo\Databases\Databases', 'templates'));
    $this->config = $this->config['Databases']['default'];
    $this->config->defaults = array(
      'server' => 'localhost',
      'database' => strtolower($this->app->name),
      'filename' => $this->p('user', 'db.sqlite3'),
    );
  }

  /**
   * Action for selecting database driver.
   */
  public function select() {
    if (isset($this->config['driver']))
      return $this->Setup->done();
    $this->title = tr('Welcome to %1', $this->app->name);
    $this->drivers = $this->DatabaseDrivers->listDrivers();
    if ($this->request->hasValidData()) {
      foreach ($this->drivers as $driver) {
        if ($driver['isAvailable'] and
             isset($this->request->data[$driver['driver']])) {
          $this->config['driver'] = $driver['driver'];
          if ($this->config->save()) {
            return $this->Setup->done();
          }
          else {
            return $this->saveConfig();
          }
        }
      }
    }
    return $this->render();
  }

  /**
   * Get label for a driver option.
   * @param string $option Option name.
   * @return string Translated label.
   */
  private function getOptionLabel($option) {
    switch ($option) {
      case 'tablePrefix':
        return tr('Table prefix');
      default:
        return tr(ucfirst($option));
    }
  }

  /**
   * Action for configuring database driver.
   */
  public function configure() {
    if (!isset($this->config['driver']))
      return $this->Setup->setState('selectDriver', false);
    $this->driver = $this->DatabaseDrivers->checkDriver($this->config['driver']);
    if (!isset($this->driver) or $this->driver['isAvailable'] !== true) {
      unset($this->config['driver']);
      if ($this->config->save())
        return $this->Setup->setState('selectDriver', false);
      else
        return $this->saveConfig();
    }
    $this->title = tr('Welcome to %1', $this->app->name);
    $this->setupForm = new Form('setup');
    $this->exception = null;
    foreach ($this->driver['requiredOptions'] as $option) {
      $this->setupForm->addString($option, $this->getOptionLabel($option));
    }
    foreach ($this->driver['optionalOptions'] as $option) {
      $this->setupForm->addString($option, $this->getOptionLabel($option), false);
    }
    if ($this->request->hasValidData()) {
      $this->setupForm->addData($this->request->data['setup']);
      if (isset($this->request->data['cancel'])) {
        unset($this->config['driver']);
        if ($this->config->save())
          return $this->Setup->setState('selectDriver', false);
        else
          return $this->saveConfig();
      }
      else if ($this->setupForm->isValid()) {
        $driver = $this->driver['driver'];
        $class = 'Jivoo\Databases\Drivers\\' . $driver . '\\' . $driver . 'Database';
        try {
          new $class($this->app, new DatabaseSchema(), $this->request->data['setup']);
          $options = array_flip(
            array_merge($this->driver['requiredOptions'],
              $this->driver['optionalOptions']
            )
          );
          foreach ($this->request->data['setup'] as $key => $value) {
            if (isset($options[$key])) {
              $this->config[$key] = $value;
            }
          }
          unset($this->config['migration']);
          if ($this->config->save())
            return $this->Setup->done();
          else
            return $this->saveConfig();
        }
        catch (DatabaseConnectionFailedException $exception) {
          $this->exception = $exception;
        }
        catch (DatabaseSelectFailedException $exception) {
          $this->exception = $exception;
        }
      }
    }
    else {
      $this->setupForm->addData($this->config->getArray());
    }
    return $this->render();
  }
}
