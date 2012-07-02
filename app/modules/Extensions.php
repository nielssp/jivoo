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
class Extensions extends ModuleBase {
  private $info = array();
  private $installed = array();
  
  private $extensions = array();

  private $loading = array();

  protected function init() {
    if (!$this->m->Configuration->exists('extensions.installed')) {
      $this->m->Configuration->set('extensions.installed', '');
      $preinstall = explode(' ', PREINSTALL_EXTENSIONS);
      foreach ($preinstall as $extension) {
        if (!empty($extension)) {
          $this->install($extension);
        }
      }
    }
    
    $this->installed = explode(
      ' ', $this->m->Configuration->get('extensions.installed')
    );

    $this->m->Backend->addPage('settings', 'extensions', tr('Extensions'), array($this, 'extensionsController'), 2);
    
    // Load installed extensions when all modules are loaded and initialized
    Hooks::attach('modulesLoaded', array($this, 'loadExtensions'));
  }
  
  public function loadExtensions() {
    foreach ($this->installed as $extension) {
      if (!empty($extension)) {
        $this->loadExtension($extension);
      }
    }
  }
  
  public function request($extension) {
    if (!isset($this->extensions[$extension])) {
      return FALSE;
    }
    return $this->extensions[$extension];
  }
  
  private function loadExtension($extension) {
    $extension = className($extension);
    if (!isset($this->extensions[$extension])) {
      if (isset($this->loading[$extension])) {
        throw new ExtensionInvalidException(tr('Circular dependency detected when attempting to load the "%1" extension.', $extension));
      }
      $this->loading[$extension] = TRUE;
      if (!file_exists(p(EXTENSIONS . $extension . '/' . $extension . '.php'))) {
        throw new ExtensionNotFoundException(tr('The "%1" extension could not be found', $extension));
      }
      require_once(p(EXTENSIONS . $extension . '/' . $extension . '.php'));
      if (!class_exists($extension)) {
        throw new ExtensionInvalidException(tr('The "%1" extension does not have a main class', $extension));
      }
//      $reflection = new ReflectionClass($className);
//      if (!$reflection->isSubclassOf('ExtensionBase')) {
//        throw new ExtensionInvalidException(tr('The "%1" extension is invalid', $extension));
//      }
      $info = $this->getInfo($extension);
      if (!$info) {
        throw new ExtensionInvalidException(tr('The "%1" extension is invalid', $extension));
      }
      $modules = array();
      $extensions = array();
      foreach ($info['dependencies']['extensions'] as $dependency => $versionInfo) {
        if ($dependency == $extension) {
          throw new ExtensionInvalidException(tr('The "%1" extension depends on itself', $extension));
        }
        try {
          $extensions[className($dependency)] = $this->loadExtension($dependency);
        }
        catch (ExtensionNotFoundException $ex) {
          $this->uninstall($extension);
          return FALSE;
        }
      }
      foreach ($info['dependencies']['modules'] as $dependency => $versionInfo) {
        $module = $this->Core->requestModule($dependency);
        /** @todo Do this when installing.. */
        // $version = $this->core->getVersion($dependency);
        if ($module !== FALSE) { // AND compareDependencyVersions($version, $versionInfo)) {
          $modules[$dependency] = $module; 
        }
        else {
          $this->uninstall($extension);
          return FALSE;
        }
      }
      $config = $this->m->Configuration->getSubset('extensions.config.' . $extension);
      //$this->extensions[$extension] = $reflection->newInstanceArgs(array($arguments, $config));
      $this->extensions[$extension] = new $extension($modules, $extensions, $config);
    }
    return $this->extensions[$extension];
  }
  
  private function updateConfig() {
    $this->m->Configuration->set(
      'extensions.installed', implode(' ', $this->installed)
    );
  }
  
  public function getInfo($extension) {
    $extension = className($extension);
    if (isset($this->info[$extension])) {
      return $this->info[$extension];
    }
    $meta = readFileMeta(p(EXTENSIONS . $extension . '/' . $extension . '.php'));
    if (!$meta OR $meta['type'] != 'extension') {
      return FALSE;
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
    $this->m->Configuration->delete('extensions.config.' . $extension);
  }
  
  public function extensionsController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['title'] = tr('Extensions');
    $this->m->Templates->renderTemplate('backend/about.html', $templateData);
  }

}

class ExtensionNotFoundException extends Exception { }
class ExtensionInvalidException extends Exception { }
