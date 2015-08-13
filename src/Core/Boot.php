<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\Console\Shell;
use Jivoo\Core\I18n\I18n;
use Jivoo\Core\Cache\StoreCache;
use Psr\Log\LogLevel;
use Jivoo\Core\Store\SerializedStore;
use Jivoo\Core\Cache\StorePool;

/**
 * The default application initialization class. Extend this class and override
 * the {@see boot} method to customize the initialization process. The defeault
 * implementation supports the environments 'production', 'development', and
 * 'cli'.
 */
class Boot extends Module {
  /**
   * @var string[] List of valid environments used by {@see boot} to select a
   * method.
   */
  protected $environments = array('production', 'development', 'cli');
  
  /**
   * Construct boot object.
   * @param App $app Application.
   */
  public final function __construct(App $app) {
    parent::__construct($app);
  }
  
  protected function load($module) {
    return $this->m->load($module);
  }
  
  protected function import($module) {
    return $this->m->import($module);
  }
  
  public function boot($environment) {    
    if (!in_array($environment, $this->environments))
      throw new InvalidEnvironmentException(tr('Undefined environment: %1', $environment));

    if (isset($this->app->manifest['defaultConfig']))
      $this->config->defaults = $this->app->manifest['defaultConfig'];
    
    $envConf = $this->p('app/environments/' . $environment . '.php');
    if (file_exists($envConf))
      $this->config->override = include $envConf;

    $this->$environment();
  }
  
  protected function development() {
    $this->config->defaults = array(
      'core' => array(
        'showExceptions' => true,
        'logLevel' => LogLevel::DEBUG,
        'createCrashReports' => false
      )
    );
    
    $this->production();
  }
  
  protected function production() {
    $this->config->defaults = array(
      'core' => array(
        'showExceptions' => false,
        'logLevel' => LogLevel::WARNING,
        'createCrashReports' => true,
        'showReference' => true
      )
    );
    
    if (Utilities::dirExists($this->p('cache'))) {
      $store = new SerializedStore($this->p('cache/i18n.s'));
      if ($store->touch()) {
        I18n::setCache(new StorePool($store));
      }
    }
    
    if (isset($this->config['i18n']['language']))
      I18n::setLanguage($this->config['i18n']['language']);
    

    I18n::loadFrom($this->p('Core', 'languages'));
    I18n::loadFrom($this->p('app', 'languages'));

    $modules = $this->modules;
    if (isset($this->app->manifest['modules']))
      $modules = $this->app->manifest['modules'];

    $this->m->import($modules);

    $listeners = $this->p('app/Listeners');
    if (is_dir($listeners)) {
      $files = scandir($listeners);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) and $split[1] == 'php') {
            $listener = $this->app->n('Listeners\\' . $split[0]);
            Utilities::assumeSubclassOf($listener, 'Jivoo\Core\AppListener');
            $this->app->attachEventListener(new $listener($this->app));
          }
        }
      }
    }

    $this->m->load($modules);
  }
  
  protected function cli() {
    // TODO: Load subset of modules (e.g. not Routing?) 
    $this->shell->parseArguments();
    $this->shell->run();
  }
}