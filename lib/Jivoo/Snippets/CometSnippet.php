<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Routing\TextResponse;
use Jivoo\Routing\Http;

/**
 * Used for pushing data to the client side using long polling.
 */
abstract class CometSnippet extends Snippet {
  /**
   * @var float Number of calls to {@see update()} per second.
   */
  protected $refreshRate = 1.0;
  
  /**
   * Update code, e.g. check for state changes.
   * @return null|mixed Data to push to browser, or null for no updates.
   */
  protected abstract function update();
  
  /**
   * {@inheritdoc}
   */
  public function get() {
    $max = floor(ini_get('max_execution_time') * 0.9);
    $start = $_SERVER['REQUEST_TIME'];
    $end = $start + $max;
    $delay = round(1 / $this->refreshRate * 1000000);
    $this->session->close();
    while (true) {
      usleep($delay);
      $data = $this->update();
      if (isset($data))
        return $data;
      if (time() >= $end)
        return new TextResponse(Http::NO_CONTENT, 'text/plain', '');
    }
  }
}