<?php
// Module
// Name           : Extensions
// Version        : 0.2.0
// Description    : The PeanutCMS extension system
// Author         : PeanutCMS
// Dependencies   : errors configuration database routes templates http
//                  users backend

/*
 * Extension system
 *
 * @package PeanutCMS
 */

/**
 * Extensions class
 */
class Extensions implements IModule {

  private $core;
  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;
  private $users;
  private $backend;
  
  private $info = array();
  private $installed = array();
  
  private $extensions = array();

  public function __construct(Core $core) {
    $this->core = $core;
    $this->database = $this->core->database;
    $this->actions = $this->core->actions;
    $this->routes = $this->core->routes;
    $this->http = $this->core->http;
    $this->templates = $this->core->templates;
    $this->errors = $this->core->errors;
    $this->configuration = $this->core->configuration;
    $this->users = $this->core->users;
    $this->backend = $this->core->backend;

    if (!$this->configuration->exists('extensions.installed')) {
      $this->configuration->set('extensions.installed', '');
      $preinstall = explode(' ', PREINSTALL_EXTENSIONS);
      foreach ($preinstall as $extension) {
        if (!empty($extension)) {
          $this->install($extension);
        }
      }
    }
    
    $this->installed = explode(
      ' ', $this->configuration->get('extensions.installed')
    );
    
    foreach ($this->installed as $extension) {
      if (!empty($extension)) {
        $this->loadExtension($extension);
      }
    }

    $this->backend->addPage('settings', 'extensions', tr('Extensions'), array($this, 'extensionsController'), 2);
  }
  
  private function loadExtension($extension) {
    if (!isset($this->modules[$module])) {
      if (!file_exists(p(EXTENSIONS . $extension . '/' . $extension . '.class.php'))) {
        throw new ExtensionNotFoundException(tr('The "%1" extension could not be found', $extension));
      }
      require_once(p(EXTENSIONS . $extension . '/' . $extension . '.class.php'));
      $className = fileClassName($extension);
      if (!class_exists($className)) {
        throw new ExtensionInvalidException(tr('The "%1" extension does not have a main class', $extension));
      }
      $reflection = new ReflectionClass($className);
      if (!$reflection->isSubclassOf('ExtensionBase')) {
        throw new ExtensionInvalidException(tr('The "%1" extension is invalid', $extension));
      }
      $info = $this->getInfo($extension);
      if (!$info) {
        throw new ExtensionInvalidException(tr('The "%1" extension is invalid', $extension));
      }
      $dependencies = $info['dependencies'];
      $arguments = array();
      foreach ($dependencies as $dependency) {
        $module = $this->core->requestModule($dependency);
        if ($module !== FALSE) {
          $arguments[$dependency] = $module; 
        }
        else {
          $this->uninstall($extension);
          return FALSE;
        }
      }
      $config = $this->configuration->getSubset('extensions.config.' . $extension);
      $this->extensions[$extension] = $reflection->newInstanceArgs(array($arguments, $config));
    }
    return $this->extensions[$extension];
  }
  
  private function updateConfig() {
    $this->configuration->set(
      'extensions.installed', implode(' ', $this->installed)
    );
  }
  
  public function getInfo($extension) {
    if (isset($this->info[$extension])) {
      return $this->info[$extension];
    }
    $meta = readFileMeta(p(EXTENSIONS . $extension . '/' . $extension . '.class.php'));
    if (!$meta OR $meta['type'] != 'extension') {
      return FALSE;
    }
    if (!isset($meta['name'])) {
      $meta['name'] = fileClassName($extension);
    }
    if (!isset($meta['dependencies'])) {
      $meta['dependencies'] = array();
    }
    else {
      $meta['dependencies'] = explode(' ', $meta['dependencies']);
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
    if ($this->getInfo($extension) === FALSE) {
      return;
    }
    $this->installed[] = $extension;
    $this->updateConfig(); 
  }
  
  public function uninstall($extension, $deleteConfig = FALSE) {
    $key = array_search($extension, $this->installed);
    if ($key === FALSE) {
      return;
    }
    unset($this->installed[$key]);
    $this->updateConfig();
    if ($deleteConfig) {
      $this->unconfigure($extension);
    }
  }
  
  public function unconfigure($extension) {
    $this->configuration->delete('extensions.config.' . $extension);
  }
  
  public function extensionsController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['title'] = tr('Extensions');
    $this->templates->renderTemplate('backend/about.html', $templateData);
  }

}

class ExtensionNotFoundException extends Exception { }
class ExtensionInvalidException extends Exception { }
