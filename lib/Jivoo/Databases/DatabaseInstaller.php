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
use Jivoo\Setup\InstallerSnippet;

/**
 * Controller for setting up database. 
 */
class DatabaseInstaller extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html', 'Form', 'Jivoo\Databases\DatabaseDrivers');

  protected function setup() {
    $this->appendStep('select');
    $this->appendStep('configure', true);
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
  public function select($data) {
    if (isset($this->config['driver']))
      return $this->next();
    $this->viewData['title'] = tr('Select database driver');
    $this->viewData['drivers'] = $this->DatabaseDrivers->listDrivers();
    $this->viewData['enableNext'] = false;
    if (isset($data)) {
      foreach ($this->viewData['drivers'] as $driver) {
        if ($driver['isAvailable'] and isset($data[$driver['driver']])) {
          $this->config['driver'] = $driver['driver'];
          return $this->saveConfig();
        }
      }
    }
    return $this->render();
  }
  
  public function undoSelect() {
    unset($this->config['driver']);
    return $this->saveConfig();
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
  public function configure($data) {
    if (!isset($this->config['driver']))
      return $this->back();
    $driver = $this->DatabaseDrivers->checkDriver($this->config['driver']);
    if (!isset($driver) or $driver['isAvailable'] !== true) {
      unset($this->config['driver']);
      if ($this->config->save())
        return $this->back();
      else
        return $this->saveConfig();
    }
    $this->viewData['title'] = tr('Configure %1', $driver['name']);
    $this->viewData['exception'] = null;
    foreach ($driver['requiredOptions'] as $option) {
      $this->form->addString($option, $this->getOptionLabel($option));
    }
    foreach ($driver['optionalOptions'] as $option) {
      $this->form->addString($option, $this->getOptionLabel($option), false);
    }
    if (isset($data)) {
      $this->form->addData($data);
      if ($this->form->isValid()) {
        $class = 'Jivoo\Databases\Drivers\\' . $driver['driver'] . '\\' . $driver['driver'] . 'Database';
        try {
          new $class($this->app, new DatabaseSchema(), $data);
          $options = array_flip(
            array_merge(
              $driver['requiredOptions'],
              $driver['optionalOptions']
            )
          );
          foreach ($data as $key => $value) {
            if (isset($options[$key])) {
              $this->config[$key] = $value;
            }
          }
          unset($this->config['migration']);
          return $this->saveConfigAndContinue();
        }
        catch (DatabaseConnectionFailedException $exception) {
          $this->viewData['exception'] = $exception;
        }
        catch (DatabaseSelectFailedException $exception) {
          $this->viewData['exception'] = $exception;
        }
      }
    }
    else {
      $this->form->addData($this->config->getArray());
    }
    $this->viewData['driver'] = $driver;
    return $this->render();
  }
}
