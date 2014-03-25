<?php
/**
 * Controller for setting up database 
 * @package Jivoo\Database
 * @property-read HtmlHelper $Html Html helper
 * @property-read FormHelper $Form Form helper
 */
class DatabaseSetupController extends SetupController {

  protected $helpers = array('Html', 'Form');

  /**
   * Action for selecting database driver
   */
  public function selectDriver() {
    if ($this->config->exists('driver')) {
      $this->refresh();
    }
    $this->title = tr('Welcome to %1', $this->config->parent['app']['name']);
    $this->drivers = $this->m->Database->listDrivers();
    if ($this->request->hasValidData()) {
      foreach ($this->drivers as $driver) {
        if ($driver['isAvailable']
          AND isset($this->request->data[$driver['driver']])) {
          $this->config->set('driver', $driver['driver']);
          if ($this->config->save()) {
            return $this->redirect(null);
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
        return tr($option);
    }
  }

  /**
   * Action for configuring database driver
   */
  public function setupDriver() {
    $this->title = tr('Welcome to %1', $this->config->parent['app']['name']);
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
        $this->config->delete('driver');
        if ($this->config->save()) {
          $this->redirect(null);
        }
        else {
          return $this->saveConfig();
        }
      }
      else if ($this->setupForm->isValid()) {
        $driver = $this->driver['driver'];
        $class = $driver . 'Database';
        Lib::import('Jivoo/Database/' . $driver);
        try {
          new $class($this->request->data['setup']);
          $options = array_flip(
            array_merge($this->driver['requiredOptions'],
              $this->driver['optionalOptions']
            )
          );
          foreach ($this->request->data['setup'] as $key => $value) {
            if (isset($options[$key])) {
              $this->config->set($key, $value);
            }
          }
          $this->config->set('configured', true);
          $this->config->delete('migration');
          if ($this->config->save()) {
            return $this->redirect(null);
          }
          else {
            return $this->saveConfig();
          }
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
