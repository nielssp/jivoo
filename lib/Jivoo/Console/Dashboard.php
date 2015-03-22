<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\Snippet;

/**
 * Console dashboard
 */
class Dashboard extends Snippet {
  /**
   * {@inheritdoc}
   */
  public function before() {
    parent::before();
    $this->view->data->title = tr('Jivoo Console');
    $this->view->data->app = $this->app->appConfig;
    $this->view->data->entryScript = realpath($this->app->entryScript);
    $this->view->data->userDir = realpath($this->p('user', ''));
    $this->view->data->appDir = realpath($this->p('app', ''));
    $this->view->data->environment = $this->app->environment;
    $this->view->data->shareDir = realpath($this->p('share', ''));
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this->render();
  }
}