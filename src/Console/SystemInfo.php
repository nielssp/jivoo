<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\Snippet;

/**
 * System information viewer.
 */
class SystemInfo extends ConsoleSnippet {
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->view->data->title = tr('System Info');
    $this->view->data->app = $this->app->manifest;
    $this->view->data->entryScript = realpath($this->app->entryScript);
    $this->view->data->userDir = realpath($this->p('user', ''));
    $this->view->data->appDir = realpath($this->p('app', ''));
    $this->view->data->environment = $this->app->environment;
    $this->view->data->shareDir = realpath($this->p('share', ''));
    return parent::before();
  }
}