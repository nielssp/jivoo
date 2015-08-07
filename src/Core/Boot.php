<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\Console\Shell;
use Psr\Log\LogLevel;

/**
 * The default application initialization class. Extend this class and override
 * the {@see boot} method to customize the initialization process. The defeault
 * implementation supports the environments 'production' and 'development'.
 */
class Boot extends Module {
  /**
   * @var string[] List of valid environments used by {@see boot} to select a
   * method.
   */
  protected $environments = array('production', 'development');
  
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
    if ($this->app->isCli()) {
      $this->cli();
      return;
    }
    
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
    
    if (!isset($this->config['core']['timeZone'])) {
      $defaultTimeZone = 'UTC';
      try {
        $defaultTimeZone = @date_default_timezone_get();
      }
      catch (\ErrorException $e) { }
      $this->config['core']['timeZone'] = $defaultTimeZone;
    }
    if (!date_default_timezone_set($this->config['core']['timeZone']))
      date_default_timezone_set('UTC');

    if (isset($this->config['core']['language']))
      I18n::setLanguage($this->config['core']['language']);
    

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
    $shell = new Shell($this->app);
    $shell->parseArguments();
    $shell->run();
    echo tr('%1 %2: CLI support disabled', $this->app->name, $this->app->version);
  }
}