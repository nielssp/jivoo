<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Routing\TextResponse;
use Jivoo\Routing\Http;
use Jivoo\Core\Json;

/**
 * JSON Helper.
 * @package Jivoo\Helpers
 */
class JsonHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('View');
  
  /**
   * Create a JSON response.
   * @param mixed Data.
   */
  public function respond($response) {
    return new TextResponse(Http::OK, 'json', Json::encode($response));
  }
}
