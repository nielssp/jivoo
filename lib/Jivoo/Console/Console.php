<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Json;
use Jivoo\Core\Logger;
use Jivoo\Routing\RenderEvent;

/**
 * Developer console module.
 */
class Console extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Snippets', 'Routing', 'View', 'Assets', 'Extensions');

  /**
   * {@inheritdoc}
   */
  protected function init() {
  }
  
  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if ($this->app->noAppConfig) {
      $this->m->Routing->routes->root('snippet:Jivoo\Console\Index');
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Index');
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Configure');
      $this->view->addTemplateDir($this->p('templates'));
    }
    if ($this->config->get('enable', false) === true) {
      $asset = $this->m->Assets->getAsset('Jivoo\Console\Console', 'assets/js/console.js');
      $this->view->resources->provide('jivoo-console.js', $asset);
      $asset = $this->m->Assets->getAsset('Jivoo\Console\Console', 'assets/css/console.css');
      $this->view->resources->provide('jivoo-console.css', $asset);
      $this->view->resources->import('jivoo-console.js');
      $this->view->resources->import('jivoo-console.css');
      
      $this->m->Routing->attachEventHandler('afterRender', function(RenderEvent $event) {
        if ($event->response->type === 'text/html') {
          $body = $event->body;
          $extraVars = '<script type="text/javascript">var jivooLog = '
          . Json::encode(Logger::getLog())
          . ';</script>' . PHP_EOL;
          $event->body = substr_replace($body, $extraVars, strripos($body, '</body'), 0);
          $event->overrideBody = true;
        }
      });
      
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Dashboard');
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Generators');
    }
  }
}
