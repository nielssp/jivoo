<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Core\Utilities;
use Jivoo\Routing\TextResponse;
use Jivoo\Routing\Http;

/**
 * Helper for creating and applying skins (CSS templates).
 */
class SkinHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Css');
  
  /**
   * @var array
   */
  private $variables = array();
  
  /**
   * @var array[]
   */
  private $skins = array();

  /**
   * {@inheritdoc}
   */
  public function __get($variable) {
    if (isset($this->variables[$variable]))
      return $this->variables[$variable];
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function __set($variable, $value) {
    $this->variables[$variable] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($variable) {
    return isset($this->variables[$variable]);
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($variable) {
    unset($this->variables[$variable]);
  }
  
  /**
   * Set default values for skin variables.
   * @param string[] $vars Associative array of skin variables.
   */
  public function setDefault($vars) {
    $this->variables = array_merge($vars, $this->variables);
  }

  /**
   * Set values for skin variables.
   * @param string[] $vars Associative array of skin variables.
   */
  public function set($vars) {
    $this->variables = array_merge($this->variables, $vars);
  }

  /**
   * Add a named skin (collectionm of variable values).
   * @param string $name Skin name.
   * @param string[] $vars Associative array of skin variables.
   */
  public function addSkin($name, $vars) {
    $this->skins[$name] = $vars;
  }
  
  /**
   * Get skins.
   * @return array[] List of associative arrays of skin variables.
   */
  public function getSkins() {
    return $this->skins;
  }
  
  /**
   * Use a skin.
   * @param string $name Skin name.
   */
  public function useSkin($name) {
    if (isset($this->skins[$name]))
      $this->set($this->skins[$name]);
  }
  
  /**
   * Import another skin into the current skin template.
   * @param string $skin Skin template name.
   * @return \Jivoo\Routing\Response CSS response.
   */
  public function import($skin) {
    return $this->view->render($skin);
  }
  
  /**
   * Apply a skin template. The route should point at a dynamic asset (action or
   * snippet returning a css-file).
   * @param array|\Jivoo\Routing\Linkable|string|null $route A route, see
   * {@see \Jivoo\Routing\Routing}.
   * @param string $options
   */
  public function apply($route, $options = null) {
    if (isset($options)) {
      $route = $this->m->Routing->validateRoute($route);
      $route['query'] = $options;
    }
    $url =  $this->m->Assets->getDynamicAsset($route);
    $this->view->resources->provide($url, $url);
    $this->view->resources->closeFrame();
    $this->view->resources->import($url);
    $this->view->resources->openFrame();
  }
  
  /**
   * Render a skin template.
   * @param string $skin Skin template name.
   * @return TextResponse CSS response.
   */
  public function render($skin) {
    $content = $this->view->render($skin);
    $response = new TextResponse(Http::OK, 'css', $content);
    return $response;
  }
}
