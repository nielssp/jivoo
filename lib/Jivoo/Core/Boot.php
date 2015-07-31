<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\Console\Shell;
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
    return $this->app->load($module);
  }
  
  protected function import($module) {
    return $this->app->import($module);
  }
  
  public function boot($environment) {
    if ($this->app->isCli()) {
      $this->cli();
      return;
    }
    
    if (!in_array($environment, $this->environments))
      throw new \DomainException(tr('Undefined environment: %1', $environment));
    
    $envConf = $this->p('app', 'environments/' . $environment . '.php');
    if (file_exists($envConf))
      $this->config->override = include $envConf;

    $this->$environment();
  }
  
  protected function development() {
    $this->config->defaults = array(
      'core' => array(
        'showExceptions' => true,
        'logLevel' => Logger::ALL,
        'createCrashReports' => false
      )
    );
    
    $this->production();
  }
  
  protected function production() {
    $this->config->defaults = array(
      'core' => array(
        'showExceptions' => false,
        'logLevel' => Logger::ERROR | Logger::WARNING,
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

    if (isset($this->config['core']['language']))
      I18n::setLanguage($this->config['core']['language']);

    I18n::loadFrom($this->p('Core', 'languages'));
    I18n::loadFrom($this->p('app', 'languages'));

    $modules = $this->modules;
    if (isset($this->app->manifest['modules']))
      $modules = $this->app->manifest['modules'];

    foreach ($modules as $module)
      $this->import($module);

    if (isset($this->app->manifest['listeners'])) {
      foreach ($this->app->manifest['listeners'] as $listener) {
        $listener = $this->app->n($listener);
        Lib::assumeSubclassOf($listener, 'Jivoo\Core\AppListener');
        $this->app->attachEventListener(new $listener($this->app));
      }
    }

    foreach ($modules as $module)
      $this->load($module);
  }
  
  protected function cli() {
    $shell = new Shell($this->app);
    $shell->parseArguments();
    $shell->run();
    echo tr('%1 %2: CLI support disabled', $this->app->name, $this->app->version);
  }
}