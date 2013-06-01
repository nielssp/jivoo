<?php

class DatabaseMaintenanceController extends ApplicationController {

  protected $helpers = array('Html', 'Form');

  public function selectDriver() {
    if ($this->config
      ->exists('driver')) {
      $this->refresh();
    }
    $this->title = tr('Welcome to PeanutCMS');
    $this->drivers = $this->m
      ->Database
      ->listDrivers();
    $this->backendMenu = false;
    if ($this->request
      ->isPost() AND $this->request
          ->checkToken()) {
      foreach ($this->drivers as $driver) {
        if ($driver['isAvailable']
            AND isset($this->request
              ->data[$driver['driver']])) {
          $this->config
            ->set('driver', $driver['driver']);
          $this->refresh();
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

  public function setupDriver($driverInfo) {
    $this->title = tr('Welcome to PeanutCMS');
    $this->backendMenu = false;
    $this->driver = $driverInfo;
    $this->setupForm = new Form('setup');
    $this->exception = null;
    foreach ($driverInfo['requiredOptions'] as $option) {
      $this->setupForm
        ->addString($option, $this->getOptionLabel($option));
    }
    foreach ($driverInfo['optionalOptions'] as $option) {
      $this->setupForm
        ->addString($option, $this->getOptionLabel($option), false);
    }
    if ($this->request
      ->isPost() AND $this->request
          ->checkToken()) {
      $this->setupForm
        ->addData($this->request
          ->data['setup']);
      if (isset($this->request
        ->data['cancel'])) {
        $this->config
          ->delete('driver');
        $this->refresh();
      }
      else if ($this->setupForm
        ->isValid()) {
        $driver = $this->driver['driver'];
        $class = $driver . 'Database';
        Lib::import('Core/Database/' . $driver);
        try {
          new $class($this->request
            ->data['setup']);
          $options = array_flip(
            array_merge($driverInfo['requiredOptions'],
              $driverInfo['optionalOptions']));
          foreach ($this->request
            ->data['setup'] as $key => $value) {
            if (isset($options[$key])) {
              $this->config
                ->set($key, $value);
            }
          }
          $this->config
            ->set('configured', 'yes');
          $this->config
            ->delete('migration');
          $this->refresh();
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
      $this->setupForm
        ->addData($this->config
          ->getArray());
    }
    $this->render();
  }
}
