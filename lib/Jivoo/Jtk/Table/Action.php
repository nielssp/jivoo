<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Models\IBasicRecord;
use Jivoo\Jtk\JtkObject;

/**
 * Table row action.
 * @property string $label Action label.
 * @property string|array|Jivoo\Routing\ILinkable|null $route A route, see
 * {@see Jivoo\Routing\Routing}.
 * @property string $icon Icon path or name.
 * @property array $data Optional data.
 * @property string $method Http method, e.g. 'get' or 'post'.
 * @property string $confirmation Optional confirmation dialog text.
 */
class Action extends JtkObject {
  /**
   * Construct action.
   * @param string $label Action label.
   * @param string|array|Jivoo\Routing\ILinkable|null $route A route, see
   * {@see Jivoo\Routing\Routing}.
   * @param string $icon Icon path or name.
   */
  public function __construct($label, $route = null, $icon = null) {
    $this->label = $label;
    $this->route = $route;
    $this->icon = $icon;
    $this->data = array();
    $this->method = 'get';
  }
  
  /**
   * Get route for a record.
   * @param IBasicRecord $record Record.
   * @todo implement
   * @return string|array|Jivoo\Routing\ILinkable|null A route, see
   * {@see Jivoo\Routing\Routing}.
   */
  public function getRoute(IBasicRecord $record) {
    
  }
}
