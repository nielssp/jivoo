<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

use Jivoo\Routing\ILinkable;

/**
 * A menu action, i.e. a clickable link.
 * @property string|array|ILinkable|null $route A route, see {@see Routing}.
 * @property string $icon Icon path or name.
 */
class MenuAction extends LabelMenuItem implements ILinkable {
  
  public function __construct($label, $route = null, $icon = null) {
    parent::__construct($label);
    $this->route = $route;
    $this->icon = $icon;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoute() {
    return $this->route;
  }
}
