<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\View\Compile\Macros;
use Jivoo\View\Compile\HtmlNode;
use Jivoo\View\Compile\TextNode;

/**
 * JTK template macros (for compiled templates).
 */
class JtkMacros extends Macros {
  /**
   * {@inheritdoc}
   */
  protected $namespace = 'jtk';
  
  /**
   * Replaces the content of the node with PHP code.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _test(HtmlNode $node, $value) {
    $node->clear()->append(new TextNode('JTK is available!'));
  }
}
