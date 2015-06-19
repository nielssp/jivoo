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
use Jivoo\Core\Event;
use Jivoo\Core\ShowExceptionEvent;

/**
 * Developer console module.
 */
class Console extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Snippets', 'Routing', 'Setup', 'View', 'Assets', 'Extensions', 'Jtk');

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
   * @var string Devbar HTML if enabled.
   */
  private $devbar = null;
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
  }
  
  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if ($this->app->noManifest) {
      if (!is_dir($this->p('user', ''))) {
        if (!mkdir($this->p('user', '')))
          throw new \Exception(tr('Could not create user directory: %1', $this->p('user', '')));
      }
      $this->m->Setup->trigger('Jivoo\Console\ManifestInstaller');
      $this->m->Routing->routes->root('snippet:Jivoo\Console\Index');
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Index');
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Configure');
      $this->m->Themes->load('flatmin-base');
    }
    if ($this->config->get('enable', false) === true) {

      $this->m->Extensions->import('jquery');
      $this->m->Extensions->import('jqueryui');
      $this->m->Extensions->import('js-cookie');
      $asset = $this->m->Assets->getAsset('js/jivoo/console/console.js');
      $this->view->resources->provide('jivoo-console.js', $asset, array('jquery.js', 'jquery-ui.js', 'js.cookie.js'));
      $asset = $this->m->Assets->getAsset('css/jivoo/console/tools.css');
      $this->view->resources->provide('jivoo-tools.css', $asset);
      
      $this->devbar = $this->view->renderOnly('jivoo/console/devbar.html');
      
      $this->m->Routing->attachEventHandler('afterRender', array($this, 'injectCode'));
      $this->app->attachEventHandler('beforeShowException', array($this, 'injectCode'));
      
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\SystemInfo');
      $this->m->Routing->routes->auto('snippet:Jivoo\Console\Generators');
      
      $this->addTool('system', tr('System'), 'snippet:Jivoo\Console\SystemInfo', true);
      
      $this->addTool('generate', tr('Generate'), 'snippet:Jivoo\Console\SystemInfo', false);
      $this->addTool('i18n', tr('I18n'), 'snippet:Jivoo\Console\SystemInfo', false);
      $this->addTool('release', tr('Release'), 'snippet:Jivoo\Console\SystemInfo', false);
    }
  }
  
  public function injectCode(Event $event) {
    if (!isset($this->devbar))
      return;
    if ($event instanceof RenderEvent) {
      if ($event->response->type !== 'text/html')
        return;
      $extraIncludes = '';
    }
    else {
      assume($event instanceof ShowExceptionEvent);
      $extraIncludes = $this->view->resourceBlock();
    }
    $body = $event->body;
    $pos = strripos($body, '</body');
    if ($pos === false)
      return;
    $this->setVariable('jivooLog', Logger::getLog());
    $this->setVariable('jivooRequest', $this->request->toArray());
    $this->setVariable('jivooSession', $this->request->session->toArray());
    $this->setVariable('jivooCookies', $this->request->cookies->toArray());
    $extraVars = '<script type="text/javascript">'
      . $this->outputVariables()
      . $this->outputTools()
      . '</script>' . PHP_EOL;
    $event->body = substr_replace($body, $extraIncludes . $this->devbar . $extraVars, $pos, 0);
    $event->overrideBody = true;
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
   * @param bool $ajax Whether to use Ajax. If false, then a simple
   * link is created instead.
   * @param bool $ajaxOnly Whether to only allow Ajax (e.g. don't allow middle
   * clicking).
   */
  public function addTool($id, $name, $route, $ajax = true, $ajaxOnly = false) {
    $this->tools[$id] = array(
      'name' => $name,
      'route' => $route,
      'ajax' => $ajax,
      'ajaxOnly' => $ajaxOnly
    );
  }
  
  /**
   * Output tool creation JavaScript.
   * @return string JavaScript.
   */
  public function outputTools() {
    $output = 'if (typeof JIVOO !== "object") {';
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
      $output .= Json::encode($link);
      if ($tool['ajax'] and $tool['ajaxOnly'])
        $output .= ', true';
      $output .= ');';
    }
    $output .= '}';
    return $output;
  }
}
