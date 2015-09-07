<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\Helpers\Helper;
use Jivoo\Models\Model;
use Jivoo\Models\BasicRecord;
use Jivoo\Routing\InvalidRouteException;

/**
 * Content helper.
 */
class ContentHelper extends Helper {
  
  private $models = array();
  
  public function register(Model $model, $field) {
    $name = $model->getName();
    if (!isset($this->models[$name]))
      $this->models[$name] = array();
    $this->models[$name][$field] = true;
  }
}
