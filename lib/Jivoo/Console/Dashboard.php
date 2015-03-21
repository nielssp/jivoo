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
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this->render();
  }
}