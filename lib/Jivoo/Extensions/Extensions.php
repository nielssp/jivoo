<?php
// Module
// Name           : Extensions
// Description    : The Jivoo extension system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Database Jivoo/Routing Jivoo/Templates

/**
 * Extension system
 * @package Jivoo\Extensions
 */
class Extensions extends LoadableModule {
  
  protected $modules = array('Database', 'Routing', 'Templates');
  
  protected $events = array(
    'beforeLoadExtensions', 'beforeLoadExtension',
    'afterLoadExtension', 'afterLoadExtensions'
  );
  
  private $info = array();
  private $installed = array();

  private $extensions = array();

  private $loading = array();

  protected function init() {
    $this->config->defaults = array(
      'config' => array()
    );
    
    if (!isset($this->config['installed'])) {
      $this->config['installed'] = '';
      $preinstall = $this->app->appConfig['extensions'];
      foreach ($preinstall as $extension) {
        if (!empty($extension)) {
          $this->install($extension);
        }
      }
    }

    $this->installed = explode(' ', $this->config['installed']);

    // Load installed extensions when all modules are loaded and initialized
    $this->app->attachEventHandler('afterLoadModules', array($this, 'loadExtensions'));
  }

  public function loadExtensions() {
    $this->triggerEvent('beforeLoadExtensions');
    foreach ($this->installed as $extension) {
      if (!empty($extension)) {
        $this->getExtension($extension);
      }
    }
    $this->triggerEvent('afterLoadExtensions');
  }

  public function request($extension) {
    if (!isset($this->extensions[$extension])) {
      return false;
    }
    return $this->extensions[$extension];
  }

  public function getExtension($extension) {
    if (!isset($this->extensions[$extension])) {
      $this->triggerEvent('beforeLoadExtension', new LoadExtensionEvent($this, $extension));
      if (isset($this->loading[$extension])) {
        throw new ExtensionInvalidException(
          tr(
            'Circular dependency detected when attempting to load the "%1" extension.',
            $extension));
      }
      $this->loading[$extension] = true;
      if (!file_exists(
        $this->p('extensions', $extension . '/' . $extension . '.php'))) {
        throw new ExtensionNotFoundException(
          tr('The "%1" extension could not be found', $extension));
      }
      require_once(
        $this->p('extensions', $extension . '/' . $extension . '.php')
      );
      if (!Lib::classExists($extension, false)) {
        throw new ExtensionInvalidException(
          tr('The "%1" extension does not have a main class', $extension));
      }

      $info = $this->getInfo($extension);
      if (!$info) {
        throw new ExtensionInvalidException(
          tr('The "%1" extension is invalid', $extension));
      }
      $extensions = array();
      foreach ($info['dependencies']['extensions'] as $dependency => $versionInfo) {
        if ($dependency == $extension) {
          throw new ExtensionInvalidException(
            tr('The "%1" extension depends on itself', $extension));
        }
        try {
          $extensions[$dependency] = $this->getExtension($dependency);
        }
        catch (ExtensionNotFoundException $ex) {
          trigger_error(
            tr(
              'Extension "%1" uninstalled. Missing extension dependency: "%2".',
              $extension, $dependency), E_USER_WARNING);
          $this->uninstall($extension);
          return false;
        }
      }
      foreach ($info['dependencies']['modules'] as $dependency => $versionInfo) {
        if (!$this->app->hasModule($dependency)) {
          trigger_error(
            tr('Extension "%1" uninstalled. Missing module dependency: "%2".',
              $extension, $dependency), E_USER_WARNING);
          $this->uninstall($extension);
          return false;
        }
      }
      $config = $this->config['config']->getSubset($extension);
      $this->extensions[$extension] = new $extension($this->app, $config, $extensions);
      $this->triggerEvent('afterLoadExtension', new LoadExtensionEvent($this, $extension, $this->extensions[$extension]));
    }
    return $this->extensions[$extension];
  }
  
  public function getExtensions($names) {
    $extensions = array();
    foreach ($names as $name)
      $extensions[$name] = $this->getExtension($name);
    return $extensions;
  }

  private function updateConfig() {
    $this->config['installed'] = implode(' ', $this->installed);
  }

  public function getInfo($extension) {
    if (isset($this->info[$extension])) {
      return $this->info[$extension];
    }
    $meta = FileMeta::read(
      $this->p('extensions', $extension . '/' . $extension . '.php'));
    if (!$meta OR $meta['type'] != 'extension') {
      return false;
    }
    if (!isset($meta['name'])) {
      $meta['name'] = $extension;
    }
    $this->info[$extension] = $meta;
    return $meta;
  }

  public function isInstalled($extension) {
    return in_array($extension, $this->installed);
  }

  public function install($extension) {
    if ($this->isInstalled($extension)) {
      return;
    }
    if ($this->getInfo($extension) === false) {
      return;
    }
    $this->installed[] = $extension;
    $this->updateConfig();
  }

  public function uninstall($extension, $deleteConfig = false) {
    $key = array_search($extension, $this->installed);
    if ($key === false) {
      return;
    }
    unset($this->installed[$key]);
    $this->updateConfig();
    if ($deleteConfig) {
      $this->unconfigure($extension);
    }
  }

  public function unconfigure($extension) {
    unset($this->config['config'][$extension]);
  }

}

/**
 * Extension not found
 * @package PeanutCMS\Extensions
 */
class ExtensionNotFoundException extends Exception {}

/**
 * Extension is invalid
 * @package PeanutCMS\Extensions
 */
class ExtensionInvalidException extends Exception {}

/**
 * Event sent before and after an extension has been loaded
 */
class LoadExtensionEvent extends LoadEvent { }