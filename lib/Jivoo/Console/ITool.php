<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\ISnippet;
/**
 * A developer toolbar tool.
 */
interface ITool {
  /**
   * Get name of tool.
   * @return string Name.
   */
  public function getName();
  
  /**
   * Get snippet for tool panel.
   * @return ISnippet Snippet object.
   */
  public function getSnippet();
}