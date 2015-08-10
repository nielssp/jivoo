<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\Snippet;

/**
 * Application development control panel.
 */
class ControlPanel extends ConsoleSnippet {
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->view->data->title = tr('Control panel');
    return parent::before();
  }
}