<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

use Jivoo\Routing\ILinkable;

/**
 * A menu action, i.e. a clickable link.
 */
class MenuAction extends ILinkable {

  private $label;

  private $route;

  private $icon;

  public function __construct($label, $route = null, $icon = null) {
    $this->label = $label;
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
