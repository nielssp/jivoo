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
  protected $modules = array('Snippets', 'Routing', 'View', 'Assets', 'Extensions', 'Jtk');

  /**
   * {@inheritdoc}
   */
  protected $events = array('beforeOutputVariables');
  
  /**
   * @var mixed[] Associative array of variables and values.
   */
  private $variables = array();
  
  /**
   * @var mixed[] Associative array of tool ids and settings.
   */
  private $tools = array();
  
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
      $this->m->Themes->load('flatmin-base');
      $this->view->addTemplateDir($this->p('templates'));
    }
    if ($this->config->get('enable', false) === true) {
      $this->view->addTemplateDir($this->p('templates'));

      $this->m->Extensions->import('jquery');
      $this->m->Extensions->import('jqueryui');
      $this->m->Extensions->import('js-cookie');
      $asset = $this->m->Assets->getAsset('Jivoo\Console\Console', 'assets/js/console.js');
      $this->view->resources->provide('jivoo-console.js', $asset, array('jquery.js', 'jquery-ui.js', 'js.cookie.js'));
      $asset = $this->m->Assets->getAsset('Jivoo\Console\Console', 'assets/css/console.css');
      $this->view->resources->provide('jivoo-console.css', $asset);
      
      $devbar = $this->view->renderOnly('jivoo/console/devbar.html');
      
      $self = $this; // pre 5.4
      $this->m->Routing->attachEventHandler('afterRender', function(RenderEvent $event) use($devbar, $self) {
        if ($event->response->type === 'text/html') {
          $body = $event->body;
          $pos = strripos($body, '</body');
          if ($pos === false)
            return;
          $self->setVariable('jivooLog', Logger::getLog());
          $extraVars = '<script type="text/javascript">'
            . $self->outputVariables()
            . $self->outputTools()
            . '</script>' . PHP_EOL;
          $event->body = substr_replace($body, $devbar . $extraVars, $pos, 0);
          $event->overrideBody = true;
        }
      });
      
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Dashboard');
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Generators');
      
      $this->addTool('dashboard', tr('System'), 'snippet:Jivoo\Console\Dashboard', true);
    }
  }
  
  /**
   * Output variables to JavaScript.
   * @return string JavaScript variable assignments.
   */
  public function outputVariables() {
    $this->triggerEvent('beforeOutputVariables');
    $output = '';
    foreach ($this->variables as $variable => $value) {
      $output .= 'var ' . $variable . ' = ' . Json::encode($value) . ';';
    }
    return $output;
  }
  
  /**
   * Add a variable to the global JavaScript namespace. 
   * @param string $variable Variable name.
   * @param mixed $value Value, will be JSON encoded.
   */
  public function setVariable($variable, $value) {
    $this->variables[$variable] = $value;
  }
  
  /**
   * Get value of a variable previously set using {@see setVariable}.
   * @param string $variable Variable name.
   * @return mixed Value of variable.
   */
  public function getVariable($variable) {
    if (isset($this->variables[$variable]))
      return $this->variables[$variable];
    return null;
  }
  
  /**
   * Add an Ajax-based tool to the developer toolbar.
   * @param string $id A unique tool id.
   * @param string $name Name of tool.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param bool $ajax Whether or not to use Ajax. If false, then a simple
   * link is created instead.
   */
  public function addTool($id, $name, $route, $ajax = true) {
    $this->tools[$id] = array(
      'name' => $name,
      'route' => $route,
      'ajax' => $ajax
    );
  }
  
  /**
   * Output tool creation JavaScript.
   * @return string JavaScript.
   */
  public function outputTools() {
    $output .= 'if (typeof JIVOO !== "object") {';
    $output .= 'console.error("Jivoo module not found!");';
    $output .= '} else if (typeof JIVOO.devbar !== "object") {';
    $output .= 'console.error("Jivoo Devbar module not found!");';
    $output .= '} else {';
    foreach ($this->tools as $id => $tool) {
      if ($tool['ajax'])
        $output .= 'JIVOO.devbar.addAjaxTool(';
      else
        $output .= 'JIVOO.devbar.addLinkTool(';
      $output .= Json::encode($id) . ', ';
      $output .= Json::encode($tool['name']) . ', ';
      $link = $this->m->Routing->getLink($tool['route']);
      $output .= Json::encode($link) . ');';
    }
    $output .= '}';
    return $output;
  }
}
