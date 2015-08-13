<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\SnippetBase;

/**
 * Console snippet that automatically disabled layout for requests made with
 * Ajax.
 */
class ConsoleSnippet extends SnippetBase {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form');
  
  /**
   * {@inheritdoc}
   */
  public function before() {
    if ($this->request->isAjax() and $this->request->accepts('html'))
      $this->disableLayout();
    return null;
  }
}