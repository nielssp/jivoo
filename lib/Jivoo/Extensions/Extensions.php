<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\LoadEvent;
use Jivoo\Core\Map;
use Jivoo\Core\Json;
use Jivoo\Core\Lib;
use Jivoo\Core\Logger;

/**
 * Extension system.
 */
class Extensions extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Assets', 'View');
  
  /**
   * {@inheritdoc}
   */
  protected $events = array(
    'beforeImportExtensions', 'afterImportExtensions',
    'beforeLoadExtensions', 'beforeLoadExtension',
    'afterLoadExtension', 'afterLoadExtensions'
  );
  
  /**
   * @var string[] Associate array mapping plural to singular for extension
   * kinds (e.g. "extensions" => "extension").
   */
  private $kinds = array(
    'extensions' => 'extension'
  );
  
  /**
   * @var string[] Libraries to search for extensions.
   */
  private $libraries = array('app', 'share');
  
  /**
   * @var ExtensionInfo[] Associative array of extension names and information.
   */
  private $info = array();
  
  /**
   * @var string[] List of extensions to import.
   */
  private $importList = array();
  
  /**
   * @var ExtensionInfo[] Associative array mapping extension module names to
   * extension information.
   */
  private $loadList = array();
  
  /**
   * @var array Array of view extensions.
   */
  private $viewExtensions = array();
  
  /**
   * @var map Map of extension modules.
   */
  private $e = null;
  
  /**
   * @var array[] Array of 2-tuples of feature name and handler callback.
   */
  private $featureHandlers = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->config->defaults = array(
      'config' => array(),
      'disableBuggy' => true
    );
    
    
    if (isset($this->app->appConfig['extensions']))
      $this->importList = $this->app->appConfig['extensions'];
    
    $this->importList = $this->config->get('import', $this->importList);
    
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
  
  /**
   * Add all view extensions..
   */
  public function addViewExtensions() {
    foreach ($this->viewExtensions as $module => $veInfo) {
      $this->view->extensions->add(
        $veInfo['template'], $this->e->$module, $veInfo['hook']
      );
    }
  }
  
  /**
   * Handle "load" feature.
   * @param ExtensionInfo $info Extension information.
   */
  public function handleLoad(ExtensionInfo $info) {
    foreach ($info->load as $name) {
      $this->loadList[$name] = $info;
    }
  }
  
  /**
   * Handle "viewExtensions" feature.
   * @param ExtensionInfo $info Extension information.
   */
  public function handleViewExtensions(ExtensionInfo $info) {
    foreach ($info->viewExtensions as $veInfo) {
      $module = $veInfo['module'];
      $template = $veInfo['template'];
      $hook = isset($veInfo['hook']) ? $veInfo['hook'] : null;
//       Lib::assumeSubclassOf($module, 'Jivoo\View\IViewExtension');
      $this->loadList[$module] = $info;
      $this->viewExtensions[$module] = array(
        'template' => $template,
        'hook' => $hook
      );
    }
  }
  
  /**
   * Handle "resources" feature.
   * @param ExtensionInfo $info Extension information.
   */
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
  
  /**
   * Attach an extension feature, i.e. a handler for extension properties.
   * @param string $name Name of feature (property key).
   * @param callback $handler Handler callback, must accept an
   * {@see ExtensionInfo} object as its first parameter.
   */
  public function attachFeature($name, $handler) {
    $this->featureHandlers[] = array($name, $handler);
  }
  
  /**
   * Add extension kind.
   * @param string $kind Kind (plural), e.g. "themes".
   * @param String $infoName Name of JSON file (singular), e.g. "theme".
   */
  public function addKind($kind, $infoName) {
    $this->kinds[$kind] = $infoName;
  }

  /**
   * Get extension information.
   * @param string $extension Extension name.
   * @return ExtensionInfo|null Extension information or null if not found or
   * invalid.
   */
  public function getInfo($extension) {
    if (!isset($this->info[$extension])) {
      $dir = $this->p('extensions', $extension);
      $library = null;
      if (!file_exists($dir . '/extension.json')) {
        foreach ($this->libraries as $key) {
          $dir = $this->p($key, 'extensions/' . $extension);
          if (file_exists($dir . '/extension.json'))
            $library = $key;
        }
        if (!isset($library))
          return null;
      }
      $info = Json::decodeFile($dir . '/extension.json');
      if (!$info)
        return null;
      $this->info[$extension] = new ExtensionInfo($extension, $info, $library, $this->isEnabled($extension));
    }
    return $this->info[$extension];
  }
  
  /**
   * Import extensions and load extension modules.
   * @throws ExtensionNotFoundException If an extension was not found or invalid.
   * @throws \Exception If "disableBuggy" is not enabled and importing an
   * extension causes an exception to be thrown.
   */
  public function run() {
    // Import extensions
    $this->importList = array_unique($this->importList);
    $this->triggerEvent('beforeImportExtensions');
    foreach ($this->importList as $extension) {
      try {
        $extensionInfo = $this->getInfo($extension);
        if (!isset($extensionInfo))
          throw new ExtensionNotFoundException(tr('Extension not found or invalid: "%1"', $extension));
        Lib::import($extensionInfo->p($this->app, ''));
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
  
  /**
   * Get a module provided by an extension. Load it if has not yet been
   * loaded.
   * @param string $name Name of module.
   * @throws ExtensionNotFoundException If extension module not found in the
   * load list, i.e. if the extension that provides the module has not been 
   * imported.
   */
  public function getModule($name) {
    if (!isset($this->e->$name)) {
      if (!isset($this->loadList[$name]))
        throw new ExtensionNotFoundException(tr('Extension not in load list: "%1"', $name));
      $this->triggerEvent('beforeLoadExtension', new LoadExtensionEvent($this, $name));
      Lib::assumeSubclassOf($name, 'Jivoo\Extensions\ExtensionModule');
      $info = $this->loadList[$name];
      $this->e->$name = new $name($this->app, $info, $this->config['config'][$info->canonicalName]);
      $this->triggerEvent('afterLoadExtension', new LoadExtensionEvent($this, $name, $this->e->$name));
    }
    return $this->e->$name;
  }
  
  /**
   * Get several extension modules.
   * @param string[] $modules List of module names.
   * @return Map Map of module names and objects.
   */
  public function getModules($modules) {
    foreach ($modules as $name)
      $this->getModule($name);
    return $this->m;
  }

  /**
   * Whether or not a module is loaded.
   * @param string $name Module name.
   * @return bool True if loaded, false otherwise.
   */
  public function hasModule($name) {
    return isset($this->e->$name);
  }

  /**
   * Whether or not an extension is enabled.
   * @param string $extension Extension name.
   * @return bool True if enabled, false otherwise.
   */
  public function isEnabled($extension) {
    return in_array($extension, $this->config['import']->getArray());
  }
  
  /**
   * Check dependencies of an extension.
   * 
   * The returned array (if dependencies are missing) has the following
   * structure:
   * <code>
   *  array(
   *    'app' => '>= 1.0' // the required application version or null if valid
   *    'extensions => array(
   *      'my-ext >= 1.0' // required extension and version
   *    ),
   *    'php' => array(
   *      'mcrypt' // a required PHP extension
   *    )
   *  )
   * </code>
   * 
   * @param ExtensionInfo $info
   * @return true|array True if no dependencies are missing, otherwise
   * returns an associative array structure listing missing dependencies of
   * different categories. 
   */
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
  
  /**
   * Check an extension dependency.
   * @param string $dependency An extension name followed by an optional version
   * comparison, see {@see compareVersion()} for valid version comparisons.
   * E.g. "my-extension >= 3.5.2".
   * @return boolean True if extension exists and the version comparison
   * evalutates to true.
   */
  public function checkExtensionDependency($dependency) {
    preg_match('/^ *([^ <>=!]+) *(.*)$/', $dependency, $matches);
    if (!$this->isEnabled($matches[1]))
      return false;
    if (empty($matches[2]))
      return true;
    return $this->compareVersion($this->getInfo($matches[1])->version, $matches[2]);
  }
  
  /**
   * Perform a version comparison.
   * @param string $actualVersion Actual version, see {@see version_compare()}
   * for valid version strings.
   * @param string $versionComparison Version comparison: an operator followed
   * by a valid version string. Supported opertors are: <>, <=, >=, ==, !=, <,
   * >, and =.
   * @return boolean
   */
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
  
  /**
   * Attempt to enable an extension.
   * @param string $extension Extension name.
   * @return true|array Returns true if no dependencies are missing, otherwise
   * returns an associative array structure listing missing dependencies of
   * different categories,s ee {@see Extensions::checkDependencies()}.
   */
  public function enable($extension) {
    $missing = $this->checkDependencies($this->getInfo($extension));
    if ($missing !== true)
      return $missing;
    $this->importList[] = $extension;
    $this->config['import'] = array_unique(array_values($this->importList));
    return true;
  }
  
  /**
   * Disable an extension.
   * @param string $extension Extension name.
   */
  public function disable($extension) {
    $this->importList = array_diff($this->importList, array($extension));
    $this->config['import'] = array_unique(array_values($this->importList));
  }

  /**
   * Delete the configuration of an extension.
   * @param string $extension Extension name.
   */
  public function unconfigure($extension) {
    unset($this->config['config'][$extension]);
  }
  
  /**
   * List all available extensions.
   * @return ExtensionInfo[] List of extension information objects.
   */
  public function listAllExtensions() {
    $extensions = $this->listExtensions();
    foreach ($this->libraries as $library)
      $extensions = array_merge($extensions, $this->listExtensions($library));
    return $extensions;
  }

  /**
   * List available extensions in a library (default is user-library).
   * @param string $library Library (path key).
   * @return ExtensionInfo[] List of extension information objects.
   */
  public function listExtensions($library = null) {
    if (isset($library))
      $dir = $this->p($library, 'extensions');
    else
      $dir = $this->p('extensions', '');
    if (!is_dir($dir))
      return array();
    $files = scandir($dir);
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
 * Extension not found.
 */
class ExtensionNotFoundException extends \Exception {}

/**
 * Extension is invalid.
 */
class ExtensionInvalidException extends \Exception {}

/**
 * Event sent before and after an extension module has been loaded.
 */
class LoadExtensionEvent extends LoadEvent { }

