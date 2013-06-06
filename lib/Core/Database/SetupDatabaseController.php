<?php

class SetupDatabaseController extends SetupController {

  protected $helpers = array('Html', 'Form');

  public function selectDriver() {
    if ($this->config->exists('driver')) {
      $this->refresh();
    }
    $this->title = tr('Welcome to PeanutCMS');
    $this->drivers = $this->m->Database->listDrivers();
    if ($this->request->isPost() AND $this->request->checkToken()) {
      foreach ($this->drivers as $driver) {
        if ($driver['isAvailable']
          AND isset($this->request->data[$driver['driver']])) {
          $this->config->set('driver', $driver['driver']);
          if ($this->config->save()) {
            $this->redirect(null);
          }
          else {
            /** @todo goto Setup::saveConfig or something */
            $this->title = '!!! CONFIG ERROR !!!';
          }
        }
      }
    }
    $this->render();
  }

  private function getOptionLabel($option) {
    switch ($option) {
      case 'tablePrefix':
        return tr('Table prefix');
      default:
        return tr($option);
    }
  }

  public function setupDriver() {
    $this->title = tr('Welcome to PeanutCMS');
    $this->backendMenu = false;
    $this->setupForm = new Form('setup');
    $this->exception = null;
    foreach ($this->driver['requiredOptions'] as $option) {
      $this->setupForm->getModel()->addString($option, $this->getOptionLabel($option));
    }
    foreach ($this->driver['optionalOptions'] as $option) {
      $this->setupForm->getModel()
        ->addString($option, $this->getOptionLabel($option), false);
    }
    if ($this->request->isPost() AND $this->request->checkToken()) {
      $this->setupForm->addData($this->request->data['setup']);
      if (isset($this->request->data['cancel'])) {
        $this->config->delete('driver');
        $this->refresh();
      }
      else if ($this->setupForm->isValid()) {
        $driver = $this->driver['driver'];
        $class = $driver . 'Database';
        Lib::import('Core/Database/' . $driver);
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
            $this->redirect(null);
          }
          else {
            /** @todo goto Setup::saveConfig or something */
            $this->title = '!!! CONFIG ERROR !!!';
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
    $this->render();
  }
}
