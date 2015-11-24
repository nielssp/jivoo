<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\LoadEvent;
use Jivoo\Core\Json;
use Jivoo\Core\Utilities;
use Jivoo\Core\JsonException;
use Jivoo\Autoloader;
use Jivoo\Core\I18n\I18n;

/**
 * Extension system.
 */
class Extensions extends LoadableModule {
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
    'extensions' => array(
      'manifest' => 'extension',
      'class' => 'Jivoo\Extensions\ExtensionInfo'
    )
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
   * @var bool[] Maps extension names to a boolean: true if extension imported,
   * false if it is currently being imported.
   */
  private $imported = array();
  
  /**
   * @var ExtensionInfo[] Associative array mapping extension module names to
   * extension information.
   */
  private $loadList = array();
  
  /**
   * @var ExtensionModule
   */
  private $extensionModules = array();
  
  /**
   * @var array Array of view extensions.
   */
  private $viewExtensions = array();
  
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
    
    
    if (isset($this->app->manifest['extensions']))
      $this->importList = $this->app->manifest['extensions'];
    
    $this->importList = array_merge(
      $this->importList,
      $this->config->get('import', array())
    );
    
    $appExtensions = $this->p('app/extensions');
    if (is_dir($appExtensions)) {
      $dirs = scandir($appExtensions);
      foreach ($dirs as $extension) {
        if ($extension[0] != '.') {
          $dir = $this->p('app/extensions/' . $extension);
          if (is_dir($dir))
            $this->importList[] = $extension;
        }
      }
    }
    
    $this->attachFeature('load', array($this, 'handleLoad'));
    $this->attachFeature('include', array($this, 'handleInclude'));
    $this->attachFeature('viewExtensions', array($this, 'handleViewExtensions'));
    
    $this->attachEventHandler('afterLoadExtensions', array($this, 'addViewExtensions'));
    
    // Load installed extensions when all modules are loaded and initialized
    $this->m->units->on('allDone', array($this, 'run'));
  }
  
  /**
   * Add all view extensions..
   */
  public function addViewExtensions() {
    foreach ($this->viewExtensions as $module => $veInfo) {
      $this->m->lazy('View')->extensions->add(
        $veInfo['template'], $this->extensionModules[$module], $veInfo['hook']
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
   * Handle "include" feature.
   * @param ExtensionInfo $info Extension information.
   */
  public function handleInclude(ExtensionInfo $info) {
    if (is_string($info->include)) {
      require $info->p($this->app, $info->include);
    }
    else {
      foreach ($info->include as $file)
        require $info->p($this->app, $file);
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
      $this->loadList[$module] = $info;
      $this->viewExtensions[$module] = array(
        'template' => $template,
        'hook' => $hook
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
   * @param String $manifest Name of manifest JSON file (singular), e.g. "theme".
   * @param string $class Class to use for manifest.
   */
  public function addKind($kind, $manifest = 'extension', $class = 'Jivoo\Extensions\ExtensionInfo') {
    $this->kinds[$kind] = array(
      'manifest' => $manifest,
      'class' => $class
    );
  }

  /**
   * Get extension information.
   * @param string $extension Extension name.
   * @param string $kind Extension kind.
   * @return ExtensionInfo|null Extension information or null if not found or
   * invalid.
   */
  public function getInfo($extension, $kind = 'extensions') {
    if (!isset($this->info[$extension])) {
      $dir = $this->p($kind, $extension);
      $manifest = $this->kinds[$kind]['manifest'] . '.json';
      $library = null;
      if (!file_exists($dir . '/' . $manifest)) {
        foreach ($this->libraries as $key) {
          $dir = $this->p($key, $kind . '/' . $extension);
          if (file_exists($dir . '/' . $manifest))
            $library = $key;
        }
        if (!isset($library))
          return null;
      }
      try {
        $info = Json::decodeFile($dir . '/' . $manifest);
      }
      catch (JsonException $e) {
        $this->logger->error(tr('Error decoding JSON: %1', $dir . '/' . $manifest));
        return null;
      }
      $this->info[$extension] = new ExtensionInfo($extension, $info, $library, $this->isEnabled($extension));
    }
    return $this->info[$extension];
  }
  
  /**
   * Import extensions and load extension modules.
   * @throws InvalidExtension If an extension was not found or invalid.
   * @throws \Exception If "disableBuggy" is not enabled and importing an
   * extension causes an exception to be thrown.
   */
  public function run() {
    // Import extensions
    $this->importList = array_unique($this->importList);
    $this->triggerEvent('beforeImportExtensions');
    foreach ($this->importList as $extension) {
      try {
        $this->import($extension);
      }
      catch (\Exception $e) {
        if ($this->config['disableBuggy']) {
          $this->disable($extension);
          $this->logger->error(
            tr('Extension "%1" disabled, caused by: %2', $extension, $e->getMessage()),
            array('exception' => $e)
          );
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
      try {
        $this->getModule($name);
      }
      catch (\Exception $e) {
        if ($this->config['disableBuggy']) {
          $this->disable($info->canonicalName);
          $this->logger->error(
            tr('Extension "%1" disabled, caused by: %2', $extension, $e->getMessage()),
            array('exception' => $e)
          );
        }
        else {
          throw $e;
        }
      }
    }
    $this->triggerEvent('afterLoadExtensions');
  }
  
  /**
   * Import a single extension.
   * @param string $extension Extension name.
   */
  public function import($extension) {
    if (isset($this->imported[$extension])) {
      if (!$this->imported[$extension])
        throw new InvalidExtensionException(tr('Extension not found or invalid: "%1"', $extension));
      return;
    }
    $this->imported[$extension] = false;
    $extensionInfo = $this->getInfo($extension);
    if (!isset($extensionInfo)) {
      throw new InvalidExtensionException(tr('Extension not found or invalid: "%1"', $extension));
    }
    if (isset($extensionInfo->namespace))
      Autoloader::getInstance()->addPath($extensionInfo->namespace, $extensionInfo->p($this->app, ''));
    else
      Autoloader::getInstance()->addPath('', $extensionInfo->p($this->app, ''));
    $extensionInfo->imported = true;
    if (is_dir($extensionInfo->p($this->app, 'languages')))
      I18n::loadFrom($extensionInfo->p($this->app, 'languages'));

    foreach ($extensionInfo->loadAfter as $dependency) {
      $this->import($dependency);
      $this->getInfo($dependency)->requiredBy($extension);
    }
    
    foreach ($this->featureHandlers as $tuple) {
      list($feature, $handler) = $tuple;
      if (isset($extensionInfo->$feature))
        call_user_func($handler, $extensionInfo);
    }
    $this->imported[$extension] = true;
  }
  
  /**
   * Get a module provided by an extension. Load it if has not yet been
   * loaded.
   * @param string $name Name of module.
   * @return ExtensionModule Module.
   * @throws InvalidExtensionException If extension module not found in the
   * load list, i.e. if the extension that provides the module has not been 
   * imported.
   */
  public function getModule($name) {
    if (!isset($this->extensionModules[$name])) {
      if (!isset($this->loadList[$name]))
        throw new InvalidExtensionException(tr('Extension not in load list: "%1"', $name));
      $this->triggerEvent('beforeLoadExtension', new LoadExtensionEvent($this, $name));
      Utilities::assumeSubclassOf($name, 'Jivoo\Extensions\ExtensionModule');
      $info = $this->loadList[$name];
      $this->extensionModules[$name] = new $name($this->app, $info, $this->config['config'][$info->canonicalName]);
      $this->triggerEvent('afterLoadExtension', new LoadExtensionEvent($this, $name, $this->extensionModules[$name]));
    }
    return $this->extensionModules[$name];
  }
  
  /**
   * Get several extension modules.
   * @param string[] $modules List of module names.
   * @return ExtensionModule[] Map of module names and objects.
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
    return isset($this->extensionModules[$name]);
  }

  /**
   * Whether or not an extension is enabled.
   * @param string $extension Extension name.
   * @return bool True if enabled, false otherwise.
   */
  public function isEnabled($extension) {
    return in_array($extension, $this->config['import']->toArray());
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
    $valid = true;
    $missing = array(
      'app' => null,
      'extensions' => array(),
      'php' => array()
    );
    foreach ($info->dependencies as $extension) {
      if (!$this->checkExtensionDependency($extension)) {
        $valid = false;
        $missing['extensions'][] = $extension;
      }
    }
    foreach ($info->phpDependencies as $phpExtension) {
      if (!extension_loaded($phpExtension)) {
        $valid = false;
        $missing['php'][] = $phpExtension;
      }
    }
    if (isset($info->appName) and $this->app->name != $info->appName) {
      $valid = false;
      $missing['app'] = $info->appName;
    }
    else if (isset($info->appVersion) and !$this->compareVersion($this->app->version, $info->appVersion)) {
      $valid = false;
      $missing['app'] = $info->appVersion;
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
    $info = $this->getInfo($matches[1]);
    if (!$info->isLibrary() and !$this->isEnabled($matches[1]))
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
    $info = $this->getInfo($extension);
    if ($info->isLibrary())
      return true;
    $missing = $this->checkDependencies($this->getInfo($extension));
    if ($missing !== true)
      return $missing;
    $this->importList[] = $extension;
    $imports = $this->config->get('import', array());
    $imports[] = $extension;
    $this->config['import'] = $imports;
    return true;
  }
  
  /**
   * Disable an extension.
   * @param string $extension Extension name.
   */
  public function disable($extension) {
    $imports = $this->config->get('import', array());
    $imports = array_diff($imports, array($extension));
    $this->config['import'] = $imports;
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
        if ($file[0] != '.') {
          $info = $this->getInfo($file);
          if (isset($info))
            $extensions[] = $info;
          else
            $this->logger->error(tr('Invalid extension: %1', $file));
        }
      }
    }
    return $extensions;
  }
}

/**
 * Event sent before and after an extension module has been loaded.
 */
class LoadExtensionEvent extends LoadEvent { }

