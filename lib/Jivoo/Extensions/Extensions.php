<?php
// Module
// Name           : Extensions
// Description    : The Jivoo extension system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Routing Jivoo/Templates

/**
 * Extension system
 * @package Jivoo\Extensions
 */
class Extensions extends LoadableModule {
  
  protected $modules = array('Assets');
  
  protected $events = array(
    'beforeImportExtensions', 'afterImportExtensions',
    'beforeLoadExtensions', 'beforeLoadExtension',
    'afterLoadExtension', 'afterLoadExtensions'
  );
  
  private $info = array();
  private $installed = array();

  private $extensions = array();

  private $loading = array();
  
  private $loadList = array();
  
  private $e = null;
  
  private $featureHandlers = array();

  protected function init() {
    $this->config->defaults = array(
      'config' => array()
    );
    
    if (!isset($this->config['import']))
      $this->config['import'] = $this->app->appConfig['extensions']; 
    
    $this->e = new Map();

    // Load installed extensions when all modules are loaded and initialized
    $this->app->attachEventHandler('afterLoadModules', array($this, 'run'));
    
    $this->attachFeature('load', array($this, 'handleLoad'));
    $this->attachFeature('assets', array($this, 'handleAssets'));
  }
  
  public function handleLoad(ExtensionInfo $info) {
    foreach ($info->load as $name) {
      $this->loadList[$name] = $info;
    }
  }
  
  public function handleAssets(ExtensionInfo $info) {
    foreach ($info->assets as $name => $asset) {
      $dependencies = isset($asset['dependencies']) ? $asset['dependencies'] : array();
      $this->view->provide(
        $name,
        $this->m->Assets->getAsset(
          'extensions',
          $info->dir . '/' . $info->replaceVariables($asset['file'])
        ),
        $dependencies
      );
    }
  }
  
  public function attachFeature($name, $handler) {
    $this->featureHandlers[] = array($name, $handler);
  }

  public function run() {
    // Import extensions
    $this->triggerEvent('beforeImportExtensions');
    foreach ($this->config['import'] as $extension) {
      try {
        $dir = $this->p('extensions', $extension);
        if (!file_exists($dir . '/extension.json'))
          throw new ExtensionNotFoundException(tr('Extension not found: "%1"', $extension));
        $info = Json::decodeFile($dir . '/extension.json');
        if (!$info)
          throw new ExtensionInvalidException(tr('Extension invalid: "%1"', $extension));
        Lib::addIncludePath($dir);
        $extensionInfo = new ExtensionInfo($extension, $info);
        foreach ($this->featureHandlers as $tuple) {
          list($feature, $handler) = $tuple;
          if (isset($extensionInfo->$feature))
            call_user_func($handler, $extensionInfo);
        }
      }
      catch (Exception $e) {
        $this->disable($extension);
        Logger::error(tr('Extension "%1" disabled, caused by:', $extension));
        Logger::logException($e);
      }
    }
    $this->triggerEvent('afterImportExtensions');
    
    // Load extension modules
    $this->triggerEvent('beforeLoadExtensions');
    foreach ($this->loadList as $name) {
      $this->getModule($name);
    }
    $this->triggerEvent('afterLoadExtensions');
  }

  public function request($extension) {
    if (!isset($this->extensions[$extension])) {
      return false;
    }
    return $this->extensions[$extension];
  }
  
  public function getModule($name) {
    if (!isset($this->e->$name)) {
      $this->triggerEvent('beforeLoadExtension', new LoadExtensionEvent($this, $name));
      Lib::assumeSubclassOf($name, 'ExtensionModule');
      $extension = $this->loadList[$name];
      $dir = $extension->dir;
      $this->e->$name = new $name($this->app, $this->config['config'][$name], $dir);
      $this->triggerEvent('afterLoadExtension', new LoadExtensionEvent($this, $name, $this->e->$name));
    }
    return $this->e->$name;
  }
  
  public function getModules($modules) {
    foreach ($modules as $name)
      $this->getModule($name);
    return $this->m;
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
  
  public function enable($extension) {
    $this->config['import'][] = $extension;
  }
  
  public function disable($extension) {
    $this->config['import'] = array_diff($this->config['import']->getArray(), array($extension));
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
 * Event sent before and after an extension module has been loaded
 */
class LoadExtensionEvent extends LoadEvent { }

