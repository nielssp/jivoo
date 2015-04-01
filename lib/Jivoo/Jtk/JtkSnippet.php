<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Snippets\Snippet;

/**
 * A toolkit snippet.
 */
class JtkSnippet extends Snippet {

  protected $autoSetters = array();
  
  public function __call($method, $parameters) {
    if (in_array($method, $this->autoSetters)) {
      $this->viewData[$method] = $parameters[0];
      return $this;
    }
    return parent::__call($method, $parameters);
  }
  
  public function __toString() {
    return $this();
  }
}