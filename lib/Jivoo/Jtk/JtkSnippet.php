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
  /**
   * {@inheritdoc}
   */
  protected $parameters = array('options');
  
  /**
   * @var array Default options.
   */
  protected $defaultOptions = array();

  public function before() {
    $this->viewData['options'] = array_merge($this->defaultOptions, $this->options);
    return null;
  }
}