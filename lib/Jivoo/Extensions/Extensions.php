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
  
  protected $modules = array('Assets', 'View');
  
  protected $events = array(
    'beforeImportExtensions', 'afterImportExtensions',
    'beforeLoadExtensions', 'beforeLoadExtension',
    'afterLoadExtension', 'afterLoadExtensions'
  );
  
  private $kinds = array(
    'extensions' => 'extension'
  );
  
  private $info = array();
  private $installed = array();

  private $extensions = array();

  private $loading = array();
  
  private $importList = array();
  
  private $loadList = array();
  
  private $viewExtensions = array();
  
  private $e = null;
  
  private $featureHandlers = array();

  protected function init() {
    $this->config->defaults = array(
      'config' => array(),
      'disableBuggy' => true
    );
    
    if (!isset($this->config['import']))
      $this->config['import'] = $this->app->appConfig['extensions']; 
    
    $this->importList = $this->config['import']->getArray();
    
    $appExtensions = $this->p('app', 'extensions');
    if (is_dir($appExtensions)) {
      $dirs = scandir($appExtensions);
      foreach ($dirs as $extension) {
        if ($extension[0] != '.') {
          $dir = $this->p('app', 'extensions/' . $extension);
          if (is_dir($dir))
            $this->importList[] = $extension;
        }
      }
    }
    
    $this->e = new Map();

    // Load installed extensions when all modules are loaded and initialized
    $this->app->attachEventHandler('afterLoadModules', array($this, 'run'));
    
    $this->attachFeature('load', array($this, 'handleLoad'));
    $this->attachFeature('resources', array($this, 'handleResources'));
    $this->attachFeature('viewExtensions', array($this, 'handleViewExtensions'));
    
    $this->attachEventHandler('afterLoadExtensions', array($this, 'addViewExtensions'));
  }
  
  public function addViewExtensions() {
    foreach ($this->viewExtensions as $module => $veInfo) {
      $this->view->extensions->add(
        $veInfo['template'], $this->e->$module, $veInfo['hook']
      );
    }
  }
  
  public function handleLoad(ExtensionInfo $info) {
    foreach ($info->load as $name) {
      $this->loadList[$name] = $info;
    }
  }
  
  public function handleViewExtensions(ExtensionInfo $info) {
    foreach ($info->viewExtensions as $veInfo) {
      $module = $veInfo['module'];
      $template = $veInfo['template'];
      $hook = isset($veInfo['hook']) ? $veInfo['hook'] : null;
      Lib::assumeSubclassOf($module, 'IViewExtension');
      $this->loadList[$module] = $info;
      $this->viewExtensions[$module] = array(
        'template' => $template,
        'hook' => $hook
      );
    }
  }
  
  public function handleResources(ExtensionInfo $info) {
    foreach ($info->resources as $resource => $resInfo) {
      $dependencies = isset($resInfo['dependencies']) ? $resInfo['dependencies'] : array();
      $condition = isset($resInfo['condition']) ? $resInfo['condition'] : null;
      $this->view->resources->provide(
        $resource,
        $info->getAsset($this->m->Assets, $info->replaceVariables($resInfo['file'])),
        $dependencies,
        $condition
      );
    }
  }
  
  public function attachFeature($name, $handler) {
    $this->featureHandlers[] = array($name, $handler);
  }
  
  public function addKind($kind, $infoName) {
    $this->kinds[$kind] = $infoName;
  }

  public function getInfo($extension, $kind = 'extensions') {
    if (!isset($this->info[$extension])) {
      $dir = $this->p('extensions', $extension);
      $bundled = false;
      if (!file_exists($dir . '/extension.json')) {
        $dir = $this->p('app', 'extensions/' . $extension);
        $bundled = true;
        if (!file_exists($dir . '/extension.json'))
          return null;
      }
      $info = Json::decodeFile($dir . '/extension.json');
      if (!$info)
        return null;
      $this->info[$extension] = new ExtensionInfo($extension, $info, $bundled, $this->isEnabled($extension));
    }
    return $this->info[$extension];
  }
  
  public function run() {
    // Import extensions
    $this->importList = array_unique($this->importList);
    $this->triggerEvent('beforeImportExtensions');
    foreach ($this->importList as $extension) {
      try {
        $extensionInfo = $this->getInfo($extension);
        if (!isset($extensionInfo))
          throw new ExtensionNotFoundException(tr('Extension not found or invalid: "%1"', $extension));
        Lib::addIncludePath($extensionInfo->p($this->app, ''));
        foreach ($this->featureHandlers as $tuple) {
          list($feature, $handler) = $tuple;
          if (isset($extensionInfo->$feature))
            call_user_func($handler, $extensionInfo);
        }
      }
      catch (Exception $e) {
        if ($this->config['disableBuggy']) {
          $this->disable($extension);
          Logger::error(tr('Extension "%1" disabled, caused by:', $extension));
          Logger::logException($e);
        }
        else {
          throw $e;
        }
      }
    }
    $this->triggerEvent('afterImportExtensions');
    
    // Load extension modules
    $this->triggerEvent('beforeLoadExtensions');
    foreach ($this->loadList as $name => $info) {
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
      if (!isset($this->loadList[$name]))
        throw new ExtensionNotFoundException(tr('Extension not in load list: "%1"', $name));
      $this->triggerEvent('beforeLoadExtension', new LoadExtensionEvent($this, $name));
      Lib::assumeSubclassOf($name, 'ExtensionModule');
      $info = $this->loadList[$name];
      $this->e->$name = new $name($this->app, $info, $this->config['config'][$name]);
      $this->triggerEvent('afterLoadExtension', new LoadExtensionEvent($this, $name, $this->e->$name));
    }
    return $this->e->$name;
  }
  
  public function getModules($modules) {
    foreach ($modules as $name)
      $this->getModule($name);
    return $this->m;
  }

  public function hasModule($name) {
    return isset($this->e->$name);
  }

  public function isEnabled($extension) {
    return in_array($extension, $this->config['import']->getArray());
  }
  
  public function checkDependencies(ExtensionInfo $info) {
    if (!isset($info->dependencies))
      return true;
    $dependencies = $info->dependencies;
    $valid = true;
    $missing = array(
      'app' => null,
      'extensions' => array(),
      'php' => array()
    );
    foreach ($dependencies as $key => $value) {
      switch ($key) {
        case 'extensions':
          foreach ($value as $extension) {
            if (!$this->checkExtensionDependency($extension)) {
              $valid = false;
              $missing['extensions'][] = $extension;
            }
          }
          break;
        case 'php':
          foreach ($value as $phpExtension) {
            if (!extension_loaded($phpExtension)) {
              $valid = false;
              $missing['php'][] = $phpExtension;
            }
          }
          break;
        default:
          if ($this->app->name == $key) {
            if (!$this->compareVersion($this->app->version, $value)) {
              $valid = false;
              $missing['app'] = $value;
            }
          }
          else {
            $valid = false;
            $missing['app'] = $value;
          }
          break;
      }
    }
    if ($valid)
      return true;
    return $missing;
  }
  
  public function checkExtensionDependency($dependency) {
    preg_match('/^ *([^ <>=!]+) *(.*)$/', $dependency, $matches);
    if (!$this->isEnabled($matches[1]))
      return false;
    if (empty($matches[2]))
      return true;
    return $this->compareVersion($this->getInfo($matches[1])->version, $matches[2]);
  }
  
  public function compareVersion($actualVersion, $versionComparison) {
    while (!empty($versionComparison)) {
      if (preg_match('/^ *(<>|<=|>=|==|!=|<|>|=) *([^ <>=!]+) *(.*)$/', $versionComparison, $matches) !== 1)
        return false;
      $operator = $matches[1];
      $expectedVersion = $matches[2];
      if (!version_compare($actualVersion, $expectedVersion, $operator))
        return false; 
      $versionComparison = $matches[3];
    }
    return true;
  }
  
  public function enable($extension) {
    $missing = $this->checkDependencies($this->getInfo($extension));
    if ($missing !== true)
      return $missing;
    $this->importList[] = $extension;
    $this->config['import'] = array_unique(array_values($this->importList));
    return true;
  }
  
  public function disable($extension) {
    $this->importList = array_diff($this->importList, array($extension));
    $this->config['import'] = array_unique(array_values($this->importList));
  }

  public function unconfigure($extension) {
    unset($this->config['config'][$extension]);
  }

  public function listExtensions() {
    $files = scandir($this->p('extensions', ''));
    $extensions = array();
    if ($files !== false) {
      foreach ($files as $file) {
        $info = $this->getInfo($file);
        if (isset($info))
          $extensions[] = $info;
      }
    }
    return $extensions;
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

