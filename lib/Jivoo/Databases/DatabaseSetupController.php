<?php
/**
 * Controller for setting up database 
 * @package Jivoo\Database
 * @property-read HtmlHelper $Html Html helper
 * @property-read FormHelper $Form Form helper
 */
class DatabaseSetupController extends SetupController {

  protected $helpers = array('Html', 'Form', 'DatabaseDrivers');

  public function before() {
    $this->config = $this->config['Databases']['default'];
    $this->config->defaults = array(
      'server' => 'localhost',
      'database' => strtolower($this->app->name),
      'filename' => $this->p('user', 'db.sqlite3'),
    );
  }

  /**
   * Action for selecting database driver
   */
  public function selectDriver() {
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
   * Get label for a driver option
   * @param string $option Option name
   * @return string Translated label
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
   * Action for configuring database driver
   */
  public function setupDriver() {
    if (!isset($this->config['driver']))
      return $this->Setup->setState('DatabaseSetup::selectDriver', false);
    $this->driver = $this->DatabaseDrivers->checkDriver($this->config['driver']);
    if (!isset($this->driver) or $this->driver['isAvailable'] !== true) {
      unset($this->config['driver']);
      if ($this->config->save())
        return $this->Setup->setState('DatabaseSetup::selectDriver', false);
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
          return $this->Setup->setState('DatabaseSetup::selectDriver', false);
        else
          return $this->saveConfig();
      }
      else if ($this->setupForm->isValid()) {
        $driver = $this->driver['driver'];
        $class = $driver . 'Database';
        Lib::import('Jivoo/Databases/Common');
        Lib::import('Jivoo/Databases/Drivers/' . $driver);
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
  
  public function getRevision($table) {
    return 0;
  }
}
