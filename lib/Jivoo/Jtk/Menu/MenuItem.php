<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

use Jivoo\Jtk\JtkObject;

/**
 * A menu item. Menu items can be added to a {@see Menu}.
 * All menu items can be extended with custom properties, e.g.
 * label, icon, shortcut key, etc., using the magic getters and setters.
 * @property Menu $parent Parent menu if any.
 */
abstract class MenuItem extends JtkObject {
  /**
   * Whether or not this is a separator.
   * @return boolean True if separator.
   */
  public function isSeparator() {
    return false;
  }
  /**
   * Whether or not this is a menu.
   * @return boolean True if menu.
   */
  public function isMenu() {
    return false;
  }
  /**
   * Whether or not this is an action.
   * @return boolean True if action.
   */
  public function isAction() {
    return false;
  }
}