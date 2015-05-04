<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

/**
 * A menu item with a label.
 * @property string $label Item label.
 */
abstract class LabelMenuItem extends MenuItem {
  public function __construct($label) {
    $this->label = $label;
  }
}