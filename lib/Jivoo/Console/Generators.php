<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\Snippet;

/**
 * Generator overview.
 */
class Generators extends ConsoleSnippet {
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->view->data->title = tr('Generators');
    return parent::before();
  }
}