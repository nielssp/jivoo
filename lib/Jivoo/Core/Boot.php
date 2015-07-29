<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

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
    
    $this->load('Routing');
  }
  
  protected function cli() {
    
  }
}