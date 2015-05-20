<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\LoadModuleEvent;
use Jivoo\Routing\TextResponse;
use Jivoo\Core\Lib;

/**
 * Installation, setup, maintenance, update and recovery system..
 */
class Setup extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected static $loadAfter = array('Controllers', 'Snippets');
  
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Helpers', 'Routing');
  
  /**
   * @var string Name of current setup action.
   */
  private $current = null;
  
  private $installers = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->Helpers->addHelper('Jivoo\Setup\SetupHelper');
  }

  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if (isset($this->app->appConfig['install'])) {
      $installer = $this->app->appConfig['install'];
      if (!Lib::classExists($installer))
        $installer = $this->app->n('Snippets\\' . $installer);
      if (!$this->config[$installer]->get('done', false)) {
        $snippet = $this->getInstaller($this->app->appConfig['install']);
        $this->view->addTemplateDir($this->p('templates'));
        $response = $snippet();
        if (is_string($response))
          $response = new TextResponse($snippet->getStatus(), 'text/html', $response);
        $this->m->Routing->respond($response);
      }
    }
  }
  
  public function getInstaller($class) {
    if (!isset($this->installers[$class])) {
      $snippet = $this->m->Snippets->getSnippet($class);
      assume($snippet instanceof InstallerSnippet);
      $this->installers[$class] = $snippet;
    }
    return $this->installers[$class];
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'current':
        return $this->$property;
      case 'currentState':
        if (isset($this->current)) {
          return isset($this->config[$this->current]) and
                 $this->config[$this->current] === true;
        }
        return false;
    }
    return parnet::__get($property);
  }

  /**
   * {@inheritdoc}
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'currentState':
        if (isset($this->current)) {
          $this->config[$this->current] = $value;
        }
        return;
    }
    return parent::__set($property, $value);
  }
  
  /**
   * Get state of a setup action.
   * @param array|ILinkable|string|null $route Setup route, see {@see Routing}.
   * @return bool True if setup has finished, false otherwise.
   */
  public function getState($route) {
    $route = $this->m->Routing->validateRoute($route);
    $name = $route['dispatcher']->fromRoute($route);
    return isset($this->config[$name]) and $this->config[$name] === true;
  }
  
  /**
   * Set state of a setup action.
   * @param array|ILinkable|string|null $route Setup route, see {@see Routing}.
   * @param bool $done Whether or not the setup has finished.
   */
  public function setState($route, $done) {
    $route = $this->m->Routing->validateRoute($route);
    $name = $route['dispatcher']->fromRoute($route);
    $this->config[$name] = $done;
  }
}
