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
        $this->m->Assets->getAsset(
          'extensions',
          $info->dir . '/' . $info->replaceVariables($resInfo['file'])
        ),
        $dependencies,
        $condition
      );
    }
  }
  
  public function attachFeature($name, $handler) {
    $this->featureHandlers[] = array($name, $handler);
  }

  public function getInfo($extension) {
    $dir = $this->p('extensions', $extension);
    if (!file_exists($dir . '/extension.json'))
      return null;
    $info = Json::decodeFile($dir . '/extension.json');
    if (!$info)
      return null;
    return new ExtensionInfo($extension, $info, $this->isEnabled($extension));
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
  
  public function enable($extension) {
    //TODO check dependencies
    $import = $this->config['import']->getArray();
    $import[] = $extension;
    $this->config['import']= $import;
  }
  
  public function disable($extension) {
    $this->config['import'] = array_diff($this->config['import']->getArray(), array($extension));
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

