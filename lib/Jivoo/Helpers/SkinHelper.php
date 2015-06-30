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
  
  private $variables = array();

  public function __get($variable) {
    if (isset($this->variables[$variable]))
      return $this->variables[$variable];
    return null;
  }
  
  public function __set($variable, $value) {
    $this->variables[$variable] = $value;
  }
  
  public function __isset($variable) {
    return isset($this->variables[$variable]);
  }
  
  public function __unset($variable) {
    unset($this->variables[$variable]);
  }
  
  public function setDefault($vars) {
    $this->variables = array_merge($vars, $this->variables);
  }
  
  /**
   * Import another skin into the current skin.
   * @param string $skin Skin template name.
   */
  public function import($skin) {
    return $this->view->render($skin);
  }
  
  public function apply($route) {
    $url = $this->getLink($route);
    $this->view->resources->provide($url . '.css', $url);
    $this->view->resources->closeFrame();
    $this->view->resources->import($url . '.css');
    $this->view->resources->openFrame();
  }
  
  public function render($skin) {
    $content = $this->view->render($skin);
    $response = new TextResponse(Http::OK, 'css', $content);
    return $response;
  }
}